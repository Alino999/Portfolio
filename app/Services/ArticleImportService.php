<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use SimpleXMLElement;

class ArticleImportService
{
    public function searchGoogleNews(string $query, string $locale): array
    {
        $isFrench = $locale === 'fr';
        $response = Http::accept('application/rss+xml, application/xml, text/xml')
            ->timeout(12)
            ->get('https://news.google.com/rss/search', [
                'q' => $query,
                'hl' => $isFrench ? 'fr' : 'en-US',
                'gl' => $isFrench ? 'BF' : 'US',
                'ceid' => $isFrench ? 'BF:fr' : 'US:en',
            ]);

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                'query' => 'La recherche Google Actualités est temporairement indisponible.',
            ]);
        }

        $previousErrors = libxml_use_internal_errors(true);
        $feed = simplexml_load_string($response->body(), SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        if (! $feed) {
            throw ValidationException::withMessages([
                'query' => 'La réponse du flux de recherche n’a pas pu être lue.',
            ]);
        }

        $items = [];
        foreach ($feed->channel->item as $item) {
            $title = $this->plainText((string) $item->title);
            $url = trim((string) $item->link);

            if ($title === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            $source = $item->children()->source;
            $items[] = [
                'title' => $title,
                'excerpt' => Str::limit($this->plainText((string) $item->description), 360, ''),
                'url' => $url,
                'publisher' => $this->plainText((string) $source),
                'published_at' => $this->publishedAt((string) $item->pubDate),
                'image' => $this->rssImageUrl($item),
            ];

            if (count($items) >= 10) {
                break;
            }
        }

        return $items;
    }

    public function readPageMetadata(string $url): array
    {
        $this->assertPublicUrl($url);

        try {
            $response = Http::accept('text/html, application/xhtml+xml')
                ->withOptions(['allow_redirects' => false])
                ->timeout(12)
                ->get($url);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'source_url' => 'Impossible de joindre cette adresse. Vérifiez le lien et réessayez.',
            ]);
        }

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                'source_url' => 'La page source n’a pas pu être récupérée. Vérifiez que le lien est public.',
            ]);
        }

        $contentType = strtolower((string) $response->header('Content-Type'));
        if ($contentType !== '' && ! str_contains($contentType, 'text/html') && ! str_contains($contentType, 'application/xhtml+xml')) {
            throw ValidationException::withMessages([
                'source_url' => 'Le lien doit pointer vers une page web HTML.',
            ]);
        }

        $previousErrors = libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $document->loadHTML('<?xml encoding="UTF-8">'.$response->body(), LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $xpath = new \DOMXPath($document);
        $title = $this->firstNodeText($xpath, [
            '//meta[@property="og:title"]/@content',
            '//meta[@name="twitter:title"]/@content',
            '//title',
        ]);
        $excerpt = $this->firstNodeText($xpath, [
            '//meta[@property="og:description"]/@content',
            '//meta[@name="description"]/@content',
            '//meta[@name="twitter:description"]/@content',
        ]);
        $publisher = $this->firstNodeText($xpath, [
            '//meta[@property="og:site_name"]/@content',
            '//meta[@name="author"]/@content',
        ]);
        $image = $this->firstNodeText($xpath, [
            '//meta[@property="og:image"]/@content',
            '//meta[@name="twitter:image"]/@content',
        ]);

        if ($title === '') {
            throw ValidationException::withMessages([
                'source_url' => 'Aucun titre n’a été trouvé sur cette page.',
            ]);
        }

        return [
            'title' => $title,
            'excerpt' => Str::limit($excerpt, 360, ''),
            'url' => $url,
            'publisher' => $publisher,
            'published_at' => null,
            'image' => $this->publicImageUrl($image, $url),
        ];
    }

    private function publicImageUrl(string $image, string $pageUrl): ?string
    {
        if ($image === '') {
            return null;
        }

        if (str_starts_with($image, '//')) {
            $image = 'https:'.$image;
        } elseif (str_starts_with($image, '/')) {
            $page = parse_url($pageUrl);
            $image = ($page['scheme'] ?? 'https').'://'.($page['host'] ?? '').$image;
        } elseif (! filter_var($image, FILTER_VALIDATE_URL)) {
            return null;
        }

        if (! str_starts_with(strtolower($image), 'https://')) {
            return null;
        }

        try {
            $this->assertPublicUrl($image);
        } catch (ValidationException) {
            return null;
        }

        return $image;
    }

    private function rssImageUrl(SimpleXMLElement $item): ?string
    {
        $media = $item->children('http://search.yahoo.com/mrss/');
        foreach (['thumbnail', 'content'] as $mediaType) {
            foreach ($media->{$mediaType} ?? [] as $mediaItem) {
                $attributes = $mediaItem->attributes();
                $type = strtolower((string) ($attributes['type'] ?? ''));
                $url = trim((string) ($attributes['url'] ?? ''));

                if (($type === '' || str_starts_with($type, 'image/')) && $url !== '') {
                    $image = $this->publicImageUrl($url, 'https://news.google.com');
                    if ($image) {
                        return $image;
                    }
                }
            }
        }

        return null;
    }

    private function assertPublicUrl(string $url): void
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = trim(strtolower(rtrim($parts['host'] ?? '', '.')), '[]');

        if (! in_array($scheme, ['http', 'https'], true) || $host === '' || isset($parts['user']) || isset($parts['pass'])) {
            throw ValidationException::withMessages([
                'source_url' => 'Utilisez un lien HTTP ou HTTPS public.',
            ]);
        }

        if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            throw ValidationException::withMessages([
                'source_url' => 'Les adresses locales ou privées ne peuvent pas être importées.',
            ]);
        }

        $isIpAddress = filter_var($host, FILTER_VALIDATE_IP) !== false;
        $looksLikeEncodedIp = preg_match('/^(?:0x[0-9a-f]+|[0-9]+|[0-9.]+)$/i', $host) === 1;

        if (($isIpAddress && ! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) || $looksLikeEncodedIp) {
            throw ValidationException::withMessages([
                'source_url' => 'Les adresses locales ou privées ne peuvent pas être importées.',
            ]);
        }
    }

    private function firstNodeText(\DOMXPath $xpath, array $queries): string
    {
        foreach ($queries as $query) {
            $node = $xpath->query($query)?->item(0);
            if ($node) {
                $value = $this->plainText($node->textContent);
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return '';
    }

    private function plainText(string $value): string
    {
        return trim(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function publishedAt(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        try {
            return now()->parse($value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }
}