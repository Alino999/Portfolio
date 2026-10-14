<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleComment;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminArticleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_import_review_publish_and_delete_an_article(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        $this->post(route('admin.articles.categories.store'), [
            'name_fr' => 'Cybersécurité',
            'name_en' => 'Cybersecurity',
        ])->assertRedirect(route('admin.articles.index'));

        $category = ArticleCategory::query()->firstOrFail();
        Http::fake([
            'https://source.example/*' => Http::response(
                '<html><head><title>Backup security</title><meta property="og:title" content="Cloud backup security"><meta name="description" content="A short public summary."><meta property="og:site_name" content="Security Journal"><meta property="og:image" content="https://source.example/security/backup.jpg"></head><body><article>Full copyrighted article text must not be copied.</article></body></html>',
                200,
                ['Content-Type' => 'text/html; charset=UTF-8'],
            ),
        ]);

        $this->post(route('admin.articles.import-url'), [
            'source_url' => 'https://source.example/security/backup',
            'category_id' => $category->id,
            'locale' => 'en',
        ])->assertRedirect(route('admin.articles.index'));

        $article = Article::query()->firstOrFail();
        $this->assertSame('draft', $article->status);
        $this->assertSame('Cloud backup security', $article->title['en']);
        $this->assertSame('A short public summary.', $article->excerpt['en']);
        $this->assertSame('https://source.example/security/backup.jpg', $article->image_path);
        $this->assertStringNotContainsString('Full copyrighted article text', $article->content['en']);

        $this->get(route('admin.articles.index'))
            ->assertOk()
            ->assertSee('Cloud backup security')
            ->assertSee('Rechercher et ajouter les brouillons')
            ->assertSee('Importer en brouillon')
            ->assertSee('Modifier');
        $this->get(route('admin.articles.edit', $article))
            ->assertOk()
            ->assertSee('A short public summary.');

        $this->get(route('admin.articles.show', $article))
            ->assertOk()
            ->assertSee('Cloud backup security')
            ->assertSee('A short public summary.');

        $this->put(route('admin.articles.update', $article), [
            'article_category_id' => $category->id,
            'title_fr' => 'Sécurité des sauvegardes',
            'title_en' => 'Cloud backup security',
            'excerpt_fr' => 'Résumé français original.',
            'excerpt_en' => 'A short public summary.',
            'content_fr' => 'Contenu rédigé en français.',
            'content_en' => 'Original editorial content in English.',
            'source_url' => 'https://source.example/security/backup',
            'author_name' => 'Security Journal',
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
        ])->assertRedirect(route('admin.articles.show', $article));

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => 'published',
        ]);
        $this->assertSame('Contenu rédigé en français.', $article->fresh()->content['fr']);
        $this->get(route('admin.articles.edit', $article))
            ->assertOk()
            ->assertSee('Original editorial content in English.');

        $this->delete(route('admin.articles.destroy', $article))
            ->assertRedirect(route('admin.articles.index'));
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    public function test_google_news_search_saves_drafts_and_skips_duplicate_sources(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $category = ArticleCategory::query()->create([
            'name' => ['fr' => 'Technologie', 'en' => 'Technology'],
            'slug' => 'technology',
            'published' => true,
        ]);
        $feed = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/"><channel><item><title>Technology update - Example News</title><link>https://news.example/story/1</link><description><![CDATA[<p>Short news summary.</p>]]></description><media:thumbnail url="https://news.example/images/story.jpg" type="image/jpeg"/><pubDate>Sun, 04 Oct 2026 09:00:00 GMT</pubDate><source>Example News</source></item></channel></rss>';
        Http::fake(['news.google.com/*' => Http::response($feed, 200, ['Content-Type' => 'application/rss+xml'])]);

        $payload = [
            'category_id' => $category->id,
            'locale' => 'en',
            'query' => 'technology',
        ];

        $this->actingAs($user)
            ->post(route('admin.articles.search'), $payload)
            ->assertRedirect(route('admin.articles.index'));
        $this->post(route('admin.articles.search'), $payload)
            ->assertRedirect(route('admin.articles.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('articles', 1);
        $article = Article::query()->firstOrFail();
        $this->assertSame('draft', $article->status);
        $this->assertSame('Technology update - Example News', $article->title['en']);
        $this->assertSame('Short news summary.', $article->excerpt['en']);
        $this->assertSame('https://news.example/images/story.jpg', $article->image_path);
    }

    public function test_cybersecurity_feed_command_imports_recent_items_as_drafts_without_duplicates(): void
    {
        $recentDate = now()->toRfc2822String();
        $oldDate = now()->subDays(10)->toRfc2822String();
        $cisaFeed = sprintf(
            '<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/"><channel><item><title>Recent CISA advisory</title><link>https://www.cisa.gov/news-events/cybersecurity-advisories/1</link><description><![CDATA[<p>Recent CISA summary.</p>]]></description><media:thumbnail url="https://www.cisa.gov/sites/default/files/security-advisory.jpg" type="image/jpeg"/><pubDate>%s</pubDate></item><item><title>Old CISA advisory</title><link>https://www.cisa.gov/news-events/cybersecurity-advisories/old</link><description>Old summary.</description><pubDate>%s</pubDate></item></channel></rss>',
            $recentDate,
            $oldDate,
        );
        $certFeed = sprintf(
            '<rss version="2.0"><channel><item><title>Avis récent CERT-FR</title><link>https://www.cert.ssi.gouv.fr/avis/CERTFR-2026-AVI-9999/</link><description>Résumé récent du CERT-FR.</description><pubDate>%s</pubDate></item></channel></rss>',
            $recentDate,
        );
        Http::fake([
            'www.cisa.gov/*' => Http::response($cisaFeed, 200, ['Content-Type' => 'application/rss+xml']),
            'www.cert.ssi.gouv.fr/feed/' => Http::response($certFeed, 200, ['Content-Type' => 'application/rss+xml']),
            'www.cert.ssi.gouv.fr/avis/*' => Http::response('<html><head><title>Recent CERT advisory</title><meta property="og:image" content="https://www.cert.ssi.gouv.fr/static/images/avis-sample.jpg"></head><body></body></html>', 200, ['Content-Type' => 'text/html']),
        ]);

        $this->artisan('articles:collect-cybersecurity')->assertSuccessful();
        $this->artisan('articles:collect-cybersecurity')->assertSuccessful();

        $this->assertDatabaseCount('articles', 2);
        $this->assertDatabaseMissing('articles', ['source_url' => 'https://www.cisa.gov/news-events/cybersecurity-advisories/old']);
        $this->assertDatabaseHas('articles', [
            'source_url' => 'https://www.cisa.gov/news-events/cybersecurity-advisories/1',
            'status' => 'draft',
            'image_path' => 'https://www.cisa.gov/sites/default/files/security-advisory.jpg',
        ]);
        $this->assertDatabaseHas('articles', [
            'source_url' => 'https://www.cert.ssi.gouv.fr/avis/CERTFR-2026-AVI-9999/',
            'status' => 'draft',
            'image_path' => 'https://www.cert.ssi.gouv.fr/static/images/avis-sample.jpg',
        ]);
    }

    public function test_admin_can_bulk_publish_articles_and_moderate_only_selected_article_comments(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        $articleData = [
            'title' => ['fr' => 'Article test', 'en' => 'Test article'],
            'excerpt' => ['fr' => 'Résumé', 'en' => 'Summary'],
            'content' => ['fr' => 'Contenu complet.', 'en' => 'Full content.'],
            'status' => 'draft',
            'featured' => false,
        ];
        $article = Article::query()->create($articleData + ['slug' => 'bulk-test-article']);
        $otherArticle = Article::query()->create($articleData + ['slug' => 'other-bulk-test-article']);
        $comment = ArticleComment::query()->create([
            'article_id' => $article->id,
            'author_name' => 'Client test',
            'content' => 'Commentaire à modérer.',
            'status' => 'pending',
        ]);
        $otherComment = ArticleComment::query()->create([
            'article_id' => $otherArticle->id,
            'author_name' => 'Autre client',
            'content' => 'Commentaire à conserver.',
            'status' => 'pending',
        ]);

        $this->post(route('admin.bulk-action', 'articles'), [
            'action' => 'publish',
            'ids' => [$article->id],
        ])->assertRedirect(route('admin.articles.index'));
        $this->assertSame('published', $article->fresh()->status);

        $this->post(route('admin.bulk-action', 'article-comments'), [
            'article_id' => $article->id,
            'action' => 'publish',
            'ids' => [$comment->id],
        ])->assertRedirect(route('admin.articles.show', $article));

        $this->assertSame('approved', $comment->fresh()->status);
        $this->assertSame('pending', $otherComment->fresh()->status);

        $this->post(route('admin.bulk-action', 'article-comments'), [
            'article_id' => $article->id,
            'action' => 'delete',
            'ids' => [$otherComment->id],
        ])->assertNotFound();
        $this->assertDatabaseHas('article_comments', ['id' => $otherComment->id]);
    }

    public function test_url_import_rejects_local_addresses(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $category = ArticleCategory::query()->create([
            'name' => ['fr' => 'Test', 'en' => 'Test'],
            'slug' => 'test',
            'published' => true,
        ]);

        $this->actingAs($user);

        foreach (['http://127.0.0.1/private', 'http://[::1]/private'] as $url) {
            $this->from(route('admin.articles.index'))
                ->post(route('admin.articles.import-url'), [
                    'source_url' => $url,
                    'category_id' => $category->id,
                    'locale' => 'fr',
                ])
                ->assertSessionHasErrors('source_url');
        }

        $this->assertDatabaseCount('articles', 0);
    }
}