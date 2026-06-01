<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Fetches and parses RSS 2.0 / Atom feeds into a normalized item array.
 * Extracted from MediaOutletController so both single-outlet and
 * aggregated (city-level) endpoints can share one implementation.
 */
class RssReader
{
    private const USER_AGENT = 'VoxTerra.media/1.0 (RSS Reader)';
    private const TIMEOUT    = 10;

    /**
     * Fetch a single feed URL and return up to $limit normalized items.
     * Returns [] on any network / parse failure (never throws).
     */
    public function fetch(string $url, int $limit = 10): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get($url);

            if (!$response->successful()) {
                return [];
            }

            return $this->parse($response->body(), $limit);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Parse a raw XML string (RSS 2.0 or Atom) into normalized items.
     */
    public function parse(string $xml, int $limit = 10): array
    {
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$doc) {
            return [];
        }

        $items = [];

        // ── RSS 2.0 ────────────────────────────────────────────────────────
        if (isset($doc->channel->item)) {
            foreach ($doc->channel->item as $item) {
                $ns      = $item->getNamespaces(true);
                $media   = isset($ns['media'])   ? $item->children($ns['media'])   : null;
                $content = isset($ns['content']) ? $item->children($ns['content']) : null;

                $image = null;
                if ($media && isset($media->thumbnail)) {
                    $image = (string) $media->thumbnail->attributes()['url'] ?? null;
                } elseif ($media && isset($media->content)) {
                    $image = (string) $media->content->attributes()['url'] ?? null;
                } elseif ($content && isset($content->encoded)) {
                    preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', (string) $content->encoded, $m);
                    $image = $m[1] ?? null;
                }

                $items[] = [
                    'title'       => (string) $item->title,
                    'link'        => (string) $item->link,
                    'description' => strip_tags((string) $item->description),
                    'pub_date'    => (string) $item->pubDate,
                    'image'       => $image,
                ];

                if (count($items) >= $limit) {
                    break;
                }
            }

            return $items;
        }

        // ── Atom ───────────────────────────────────────────────────────────
        foreach ($doc->entry ?? [] as $entry) {
            $link = '';
            foreach ($entry->link as $l) {
                $rel = (string) $l->attributes()['rel'];
                if ($rel === 'alternate' || $rel === '') {
                    $link = (string) $l->attributes()['href'];
                    break;
                }
            }

            $image = null;
            if (isset($entry->content)) {
                preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', (string) $entry->content, $m);
                $image = $m[1] ?? null;
            }

            $items[] = [
                'title'       => (string) $entry->title,
                'link'        => $link,
                'description' => strip_tags((string) ($entry->summary ?? $entry->content ?? '')),
                'pub_date'    => (string) ($entry->updated ?? $entry->published ?? ''),
                'image'       => $image,
            ];

            if (count($items) >= $limit) {
                break;
            }
        }

        return $items;
    }
}
