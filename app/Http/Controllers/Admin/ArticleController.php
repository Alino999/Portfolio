<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleComment;
use App\Services\ArticleImportService;
use App\Support\LocaleHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $categories = ArticleCategory::query()->orderBy('sort_order')->orderBy('id')->get();
        $articlesQuery = Article::query()->with('category')->latest();

        if ($request->filled('category_id')) {
            $articlesQuery->where('article_category_id', $request->integer('category_id'));
        }

        if (in_array($request->query('status'), ['draft', 'published'], true)) {
            $articlesQuery->where('status', $request->query('status'));
        }

        $articles = $articlesQuery->paginate(10)->withQueryString();

        return view('admin.articles.index', compact('articles', 'categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name_fr' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
        ]);

        $slug = Str::slug($validated['name_fr']);
        if ($slug === '') {
            $slug = Str::slug($validated['name_en'] ?? 'category');
        }

        if (ArticleCategory::query()->where('slug', $slug)->exists()) {
            return back()->withErrors(['name_fr' => 'Cette catégorie existe déjà.'])->withInput();
        }

        ArticleCategory::query()->create([
            'name' => [
                'fr' => $validated['name_fr'],
                'en' => $validated['name_en'] ?? $validated['name_fr'],
            ],
            'slug' => $slug,
            'sort_order' => (int) ArticleCategory::query()->max('sort_order') + 1,
            'published' => true,
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'Catégorie ajoutée.');
    }

    public function search(Request $request, ArticleImportService $importer)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:article_categories,id'],
            'locale' => ['required', Rule::in(['fr', 'en'])],
            'query' => ['nullable', 'string', 'max:160'],
        ]);

        $category = ArticleCategory::query()->findOrFail($validated['category_id']);
        $query = trim($validated['query'] ?? '') ?: LocaleHelper::text($category->name, $validated['locale']);
        if ($query === '') {
            return back()->withErrors(['query' => 'Ajoutez un nom à la catégorie avant de lancer la recherche.']);
        }

        $results = $importer->searchGoogleNews($query, $validated['locale']);
        $created = 0;
        $duplicates = 0;

        foreach ($results as $result) {
            if (Article::query()->where('source_url', $result['url'])->exists()) {
                $duplicates++;
                continue;
            }

            $this->createDraft($result, $category, $validated['locale']);
            $created++;
        }

        if ($created === 0 && $duplicates === 0) {
            return redirect()->route('admin.articles.index')->with('error', 'Aucun résultat trouvé pour cette recherche.');
        }

        return redirect()->route('admin.articles.index')->with('success', "{$created} article(s) ajouté(s) en brouillon. {$duplicates} doublon(s) ignoré(s).");
    }

    public function importUrl(Request $request, ArticleImportService $importer)
    {
        $validated = $request->validate([
            'source_url' => ['required', 'url', 'max:2048'],
            'category_id' => ['required', 'exists:article_categories,id'],
            'locale' => ['required', Rule::in(['fr', 'en'])],
        ]);

        if (Article::query()->where('source_url', $validated['source_url'])->exists()) {
            return redirect()->route('admin.articles.index')->with('error', 'Cette adresse source a déjà été importée.');
        }

        $metadata = $importer->readPageMetadata($validated['source_url']);
        $category = ArticleCategory::query()->findOrFail($validated['category_id']);
        $this->createDraft($metadata, $category, $validated['locale']);

        return redirect()->route('admin.articles.index')->with('success', 'Article ajouté en brouillon. Vérifiez puis complétez son contenu avant publication.');
    }

    public function show(Article $article)
    {
        $article->load('category');
        $comments = $article->comments()->latest()->paginate(10);

        return view('admin.articles.show', compact('article', 'comments'));
    }

    public function edit(Article $article)
    {
        $categories = ArticleCategory::query()->orderBy('sort_order')->get();

        return view('admin.articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'article_category_id' => ['nullable', 'exists:article_categories,id'],
            'title_fr' => ['required_without:title_en', 'nullable', 'string', 'max:255'],
            'title_en' => ['required_without:title_fr', 'nullable', 'string', 'max:255'],
            'excerpt_fr' => ['nullable', 'string', 'max:1000'],
            'excerpt_en' => ['nullable', 'string', 'max:1000'],
            'content_fr' => ['nullable', 'string'],
            'content_en' => ['nullable', 'string'],
            'source_url' => ['nullable', 'url', 'max:2048', Rule::unique('articles', 'source_url')->ignore($article->id)],
            'author_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $title = $this->localizedValues($validated, 'title');
        $excerpt = $this->localizedValues($validated, 'excerpt');
        $content = $this->localizedValues($validated, 'content');

        if ($validated['status'] === 'published' && $content === []) {
            return back()->withErrors(['content_fr' => 'Ajoutez le contenu de l’article avant de le publier.'])->withInput();
        }

        $imagePath = $article->image_path;
        if ($request->boolean('remove_image')) {
            $this->deleteStoredImage($imagePath);
            $imagePath = null;
        }
        if ($request->hasFile('image')) {
            $this->deleteStoredImage($imagePath);
            $imagePath = $request->file('image')->store('blog', 'public');
        }

        $article->update([
            'article_category_id' => $validated['article_category_id'] ?? null,
            'title' => $title,
            'excerpt' => $excerpt,
            'content' => $content,
            'source_url' => $validated['source_url'] ?? null,
            'author_name' => $validated['author_name'] ?? null,
            'status' => $validated['status'],
            'published_at' => $validated['status'] === 'published'
                ? ($validated['published_at'] ?? $article->published_at ?? now())
                : ($validated['published_at'] ?? null),
            'sort_order' => $validated['sort_order'] ?? $article->sort_order,
            'featured' => $request->boolean('featured'),
            'image_path' => $imagePath,
        ]);

        return redirect()->route('admin.articles.show', $article)->with('success', 'Article mis à jour.');
    }

    public function destroy(Article $article)
    {
        $this->deleteStoredImage($article->image_path);
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Article supprimé.');
    }

    public function togglePublication(Article $article)
    {
        if ($article->status !== 'published') {
            $hasContent = collect($article->content ?? [])->contains(fn ($value) => is_string($value) && trim($value) !== '');
            if (! $hasContent) {
                return redirect()->route('admin.articles.edit', $article)
                    ->withErrors(['content_fr' => 'Ajoutez le contenu de l’article avant de le publier.']);
            }

            $article->status = 'published';
            $article->published_at ??= now();
            $message = 'Article publié sur le blogue.';
        } else {
            $article->status = 'draft';
            $message = 'Article dépublié et masqué du blogue.';
        }

        $article->save();

        return redirect()->route('admin.articles.index')->with('success', $message);
    }

    public function approveComment(Article $article, ArticleComment $comment)
    {
        abort_unless($comment->article_id === $article->id, 404);
        $comment->update(['status' => 'approved']);

        return redirect()->route('admin.articles.show', $article)->with('success', 'Commentaire approuvé.');
    }

    public function destroyComment(Article $article, ArticleComment $comment)
    {
        abort_unless($comment->article_id === $article->id, 404);
        $comment->delete();

        return redirect()->route('admin.articles.show', $article)->with('success', 'Commentaire supprimé.');
    }

    private function createDraft(array $metadata, ArticleCategory $category, string $locale): Article
    {
        $title = [$locale => $metadata['title']];
        $excerpt = $metadata['excerpt'] !== '' ? [$locale => $metadata['excerpt']] : [];

        return Article::query()->create([
            'article_category_id' => $category->id,
            'title' => $title,
            'slug' => $this->uniqueSlug($metadata['title']),
            'excerpt' => $excerpt,
            'content' => $excerpt,
            'image_path' => $metadata['image'] ?? null,
            'source_url' => $metadata['url'],
            'author_name' => $metadata['publisher'] ?: null,
            'published_at' => $metadata['published_at'],
            'status' => 'draft',
            'sort_order' => (int) Article::query()->max('sort_order') + 1,
            'featured' => false,
        ]);
    }

    private function localizedValues(array $validated, string $field): array
    {
        return array_filter([
            'fr' => trim($validated[$field.'_fr'] ?? ''),
            'en' => trim($validated[$field.'_en'] ?? ''),
        ], fn (string $value) => $value !== '');
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'article';
        $slug = $base;
        $suffix = 2;

        while (Article::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function deleteStoredImage(?string $imagePath): void
    {
        if ($imagePath && ! Str::startsWith($imagePath, ['http://', 'https://'])) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}