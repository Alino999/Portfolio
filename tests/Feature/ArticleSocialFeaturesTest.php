<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleComment;
use App\Models\ArticleLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleSocialFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_article_can_be_liked_and_comments_need_approval(): void
    {
        $article = $this->createArticle('published');
        $user = User::factory()->create(['email_verified_at' => now()]);
        $publicUrl = route('public.articles.show', $article->slug);

        $this->get($publicUrl)
            ->assertOk()
            ->assertSee('class="modal fade show d-block article-modal-backdrop"', false)
            ->assertSee('Public article')
            ->assertSee('Like');

        $this->withCookie('article_like_visitor', str_repeat('a', 64))
            ->post(route('public.articles.like', $article->slug))
            ->assertRedirect();
        $this->assertDatabaseCount('article_likes', 1);
        $this->post(route('public.articles.like', $article->slug))->assertRedirect();
        $this->assertDatabaseCount('article_likes', 0);

        $this->post(route('public.articles.comments.store', $article->slug), [
            'author_name' => 'Alex Reader',
            'content' => 'A useful article.',
        ])->assertRedirect();

        $comment = ArticleComment::query()->firstOrFail();
        $this->assertSame('pending', $comment->status);
        $this->get($publicUrl)->assertDontSee('A useful article.');

        $this->actingAs($user)
            ->patch(route('admin.articles.comments.approve', [$article, $comment]))
            ->assertRedirect(route('admin.articles.show', $article));

        $this->get($publicUrl)->assertSee('A useful article.');

        $this->actingAs($user)
            ->delete(route('admin.articles.comments.destroy', [$article, $comment]))
            ->assertRedirect(route('admin.articles.show', $article));
        $this->assertDatabaseMissing('article_comments', ['id' => $comment->id]);
    }

    public function test_admin_can_upload_an_article_image_and_publish_or_hide_the_article(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['email_verified_at' => now()]);
        $article = $this->createArticle('draft');

        $this->actingAs($user)
            ->put(route('admin.articles.update', $article), [
                'title_en' => 'Public article',
                'excerpt_en' => 'A short summary.',
                'content_en' => 'The full article content.',
                'status' => 'draft',
                'image' => UploadedFile::fake()->createWithContent(
                    'cover.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/2ioAAAAASUVORK5CYII='),
                ),
            ])
            ->assertRedirect(route('admin.articles.show', $article));

        $article->refresh();
        $this->assertNotNull($article->image_path);
        Storage::disk('public')->assertExists($article->image_path);
        $this->get(route('public.articles.show', $article->slug))->assertNotFound();

        $this->actingAs($user)
            ->patch(route('admin.articles.toggle-publication', $article))
            ->assertRedirect(route('admin.articles.index'));

        $article->refresh();
        $this->assertSame('published', $article->status);
        $this->get(route('public.articles.show', $article->slug))
            ->assertOk()
            ->assertSee('/storage/blog/')
            ->assertSee('The full article content.');

        $this->patch(route('admin.articles.toggle-publication', $article))
            ->assertRedirect(route('admin.articles.index'));
        $this->assertSame('draft', $article->fresh()->status);
        $this->get(route('public.articles.show', $article->slug))->assertNotFound();
    }

    private function createArticle(string $status): Article
    {
        static $sequence = 0;
        $sequence++;

        return Article::query()->create([
            'title' => ['fr' => 'Article public', 'en' => 'Public article'],
            'slug' => 'public-article-'.$sequence,
            'excerpt' => ['fr' => 'Résumé court.', 'en' => 'A short summary.'],
            'content' => ['fr' => 'Contenu complet.', 'en' => 'The full article content.'],
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ]);
    }
}