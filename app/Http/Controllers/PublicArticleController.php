<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleComment;
use App\Models\ArticleLike;
use App\Services\PortfolioDataService;
use Illuminate\Http\Request;

class PublicArticleController extends Controller
{
    public function __construct(private readonly PortfolioDataService $portfolioDataService) {}

    public function show(Request $request, Article $article)
    {
        abort_unless($article->status === 'published', 404);

        $article->load([
            'category',
            'comments' => fn ($query) => $query->where('status', 'approved')->latest(),
        ]);
        $visitorHash = hash('sha256', $this->visitorToken($request));
        $hasLiked = $article->likes()->where('visitor_hash', $visitorHash)->exists();
        $likesCount = $article->likes()->count();
        $settings = $this->portfolioDataService->siteSettings();

        return view('public.articles.show', compact('article', 'hasLiked', 'likesCount', 'settings'));
    }

    public function toggleLike(Request $request, Article $article)
    {
        abort_unless($article->status === 'published', 404);

        $visitorToken = $this->visitorToken($request);
        $visitorHash = hash('sha256', $visitorToken);
        $like = ArticleLike::query()
            ->where('article_id', $article->id)
            ->where('visitor_hash', $visitorHash)
            ->first();

        if ($like) {
            $like->delete();
        } else {
            ArticleLike::query()->create([
                'article_id' => $article->id,
                'visitor_hash' => $visitorHash,
            ]);
        }

        return redirect()
            ->to(route('public.articles.show', $article->slug).'#article-actions')
            ->withCookie(cookie('article_like_visitor', $visitorToken, 525600, '/', null, $request->isSecure(), true, false, 'Lax'));
    }

    public function comment(Request $request, Article $article)
    {
        abort_unless($article->status === 'published', 404);

        $validated = $request->validate([
            'author_name' => ['required', 'string', 'min:2', 'max:100'],
            'content' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        ArticleComment::query()->create([
            'article_id' => $article->id,
            'author_name' => $validated['author_name'],
            'content' => $validated['content'],
            'status' => 'pending',
        ]);

        return redirect()
            ->to(route('public.articles.show', $article->slug).'#comments')
            ->with('comment_pending', true);
    }

    private function visitorToken(Request $request): string
    {
        $token = $request->cookie('article_like_visitor');

        return is_string($token) && preg_match('/^[a-f0-9]{64}$/i', $token)
            ? $token
            : bin2hex(random_bytes(32));
    }
}