<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;

class CybersecurityFeedImportService
{
    public function __construct(private readonly ArticleImportService $articleImportService) {}

    public function collect(?int $lookbackDays = null): array
    {
        $lookbackDays = max(1, $lookbackDays ?? (int) config('cybersecurity_feeds.lookback_days', 3));
        $cutoff = now()->subDays($lookbackDays);
        $category = $this->category();
        $result = ['imported' => 0, 'duplicates' => 0, 'ignored' => 0, 'errors' => []];
        $maxItems = max(1, (int) config('cybersecurity_feeds.max_items_per_feed', 10));

        foreach (config('cybersecurity_feeds.sources', []) as $source) {
            try {
                $response = Http::accept('application/rss+xml, application/xml, text/xml')
                    ->timeout(20)
                    ->get($source['url']);

                if (! $response->successful()) {
                    throw new RuntimeException('HTTP '.$response->status());
                }

                $feed = $this->parseFeed($response->body());
                $importedFromSource = 0;

                foreach ($feed->channel->item ?? [] as $item) {
                    $title = $this->plainText((string) $item->title);
                    $url = trim((string) $item->link);
                    $publishedAt = $this->publishedAt((string) $item->pubDate);

                    if ($title === '' || ! $this->isTrustedUrl($url, $source['host']) || ! $publishedAt) {
                        $result['ignored']++;
                        continue;
                    }

                    if ($publishedAt->lt($cutoff) || $publishedAt->isFuture()) {
                        $result['ignored']++;
                        continue;
                    }

                    if (Article::query()->where('source_url', $url)->exists()) {
                        $result['duplicates']++;
                        continue;
                    }

                    $locale = $source['locale'];
                    $excerpt = Str::limit($this->plainText((string) $item->description), 360, '');
                    $imagePath = $this->feedImageUrl($item, $source);

                    if (! $imagePath) {
                        try {
                            $metadata = $this->articleImportService->readPageMetadata($url);
                            $candidateImage = $metadata['image'] ?? null;
                            if (is_string($candidateImage) && $this->isTrustedImageUrl($candidateImage, $source)) {
                                $imagePath = $candidateImage;
                            }
                        } catch (\Throwable) {
                            // Image metadata is optional; keep the text-only draft if unavailable.
                        }
                    }

                    Article::query()->create([
                        'article_category_id' => $category->id,
                        'title' => [$locale => $title],
                        'slug' => $this->uniqueSlug($title),
                        'excerpt' => $excerpt !== '' ? [$locale => $excerpt] : [],
                        'content' => [$locale => $excerpt !== '' ? $excerpt : $title],
                        'image_path' => $imagePath,
                        'source_url' => $url,
                        'author_name' => $source['name'],
                        'published_at' => $publishedAt,
                        'status' => 'draft',
                        'sort_order' => (int) Article::query()->max('sort_order') + 1,
                        'featured' => false,
                    ]);

                    $result['imported']++;
                    $importedFromSource++;
                    if ($importedFromSource >= $maxItems) {
                        break;
                    }
                }
            } catch (\Throwable $exception) {
                $result['errors'][] = $source['name'].': '.$exception->getMessage();
            }
        }

        return $result;
    }

    private function category(): ArticleCategory
    {
        $slug = (string) config('cybersecurity_feeds.category_slug', 'cybersecurity-daily');

        return ArticleCategory::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'name' => ['fr' => 'Cybersécurité', 'en' => 'Cybersecurity'],
                'sort_order' => (int) ArticleCategory::query()->max('sort_order') + 1,
                'published' => true,
            ],
        );
    }

    private function parseFeed(string $xml): SimpleXMLElement
    {
        $previousErrors = libxml_use_internal_errors(true);
        $feed = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        if (! $feed || ! isset($feed->channel)) {
            throw new RuntimeException('Le flux RSS est invalide.');
        }

        return $feed;
    }

    private function isTrustedUrl(string $url, string $trustedHost): bool
    {
        $parts = parse_url($url);

        return strtolower($parts['scheme'] ?? '') === 'https'
            && strtolower(rtrim($parts['host'] ?? '', '.')) === $trustedHost
            && ! isset($parts['user'])
            && ! isset($parts['pass']);
    }

    private function feedImageUrl(SimpleXMLElement $item, array $source): ?string
    {
        $candidates = [];
        $enclosure = $item->enclosure ?? null;
        if ($enclosure) {
            $attributes = $enclosure->attributes();
            if (str_starts_with(strtolower((string) ($attributes['type'] ?? '')), 'image/')) {
                $candidates[] = (string) ($attributes['url'] ?? '');
            }
        }

        $media = $item->children('http://search.yahoo.com/mrss/');
        foreach (['thumbnail', 'content'] as $mediaType) {
            foreach ($media->{$mediaType} ?? [] as $mediaItem) {
                $attributes = $mediaItem->attributes();
                $type = strtolower((string) ($attributes['type'] ?? ''));
                if ($type === '' || str_starts_with($type, 'image/')) {
                    $candidates[] = (string) ($attributes['url'] ?? '');
                }
            }
        }

        $description = html_entity_decode((string) $item->description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (str_contains(strtolower($description), '<img')) {
            $previousErrors = libxml_use_internal_errors(true);
            $document = new \DOMDocument();
            $document->loadHTML($description, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new \DOMXPath($document);
            foreach ($xpath->query('//img[@src]/@src') ?: [] as $imageNode) {
                $candidates[] = trim($imageNode->nodeValue ?? '');
            }
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrors);
        }

        foreach ($candidates as $candidate) {
            if ($this->isTrustedImageUrl($candidate, $source)) {
                return $candidate;
            }
        }

        return null;
    }

    private function isTrustedImageUrl(string $url, array $source): bool
    {
        $parts = parse_url($url);
        $host = strtolower(rtrim($parts['host'] ?? '', '.'));
        $path = strtolower($parts['path'] ?? '');

        if (strtolower($parts['scheme'] ?? '') !== 'https'
            || $host === ''
            || isset($parts['user'])
            || isset($parts['pass'])
            || preg_match('/(?:^|[\/_-])(logo|favicon|branding|header)(?:[\/_.-]|$)/i', $path) === 1) {
            return false;
        }

        foreach ($source['image_hosts'] ?? [$source['host']] as $trustedHost) {
            if ($host === $trustedHost || str_ends_with($host, '.'.$trustedHost)) {
                return true;
            }
        }

        return false;
    }

    private function plainText(string $value): string
    {
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($decoded);

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    private function publishedAt(string $value): ?Carbon
    {
        if (trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'cybersecurity-update';
        $slug = $base;
        $suffix = 2;

        while (Article::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}