@extends('layouts.admin')

@section('content')
@php
    $title = $article->title ?? [];
    $excerpt = $article->excerpt ?? [];
    $content = $article->content ?? [];
@endphp
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div><x-admin.icon-action :href="route('admin.articles.show', $article)" size="sm" variant="outline-secondary" icon="bi-arrow-left" label="Voir l’article" /><h1 class="h3 mt-2 mb-0">Modifier l’article</h1></div>
        @if($article->source_url)<x-admin.icon-action :href="$article->source_url" target="_blank" rel="noopener noreferrer" variant="outline-secondary" icon="bi-box-arrow-up-right" label="Ouvrir la source" />@endif
    </div>
    <form method="POST" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data" class="border rounded bg-white p-3 p-md-4">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="title-fr">Titre français</label><input id="title-fr" name="title_fr" class="form-control" maxlength="255" value="{{ old('title_fr', $title['fr'] ?? '') }}"></div>
            <div class="col-md-6"><label class="form-label" for="title-en">Title in English</label><input id="title-en" name="title_en" class="form-control" maxlength="255" value="{{ old('title_en', $title['en'] ?? '') }}"></div>
            <div class="col-md-6"><label class="form-label" for="excerpt-fr">Résumé français</label><textarea id="excerpt-fr" name="excerpt_fr" class="form-control" rows="4" maxlength="1000">{{ old('excerpt_fr', $excerpt['fr'] ?? '') }}</textarea></div>
            <div class="col-md-6"><label class="form-label" for="excerpt-en">Excerpt in English</label><textarea id="excerpt-en" name="excerpt_en" class="form-control" rows="4" maxlength="1000">{{ old('excerpt_en', $excerpt['en'] ?? '') }}</textarea></div>
            <div class="col-md-6"><label class="form-label" for="content-fr">Contenu français</label><textarea id="content-fr" name="content_fr" class="form-control" rows="10">{{ old('content_fr', $content['fr'] ?? '') }}</textarea></div>
            <div class="col-md-6"><label class="form-label" for="content-en">Content in English</label><textarea id="content-en" name="content_en" class="form-control" rows="10">{{ old('content_en', $content['en'] ?? '') }}</textarea></div>
            <div class="col-md-6">
                <label class="form-label" for="article-category">Catégorie</label>
                <select id="article-category" name="article_category_id" class="form-select">
                    <option value="">Sans catégorie</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('article_category_id', $article->article_category_id) == $category->id)>{{ App\Support\LocaleHelper::text($category->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6"><label class="form-label" for="source-url">Lien source</label><input id="source-url" type="url" name="source_url" class="form-control" maxlength="2048" value="{{ old('source_url', $article->source_url) }}"></div>
            <div class="col-md-6"><label class="form-label" for="author-name">Source / auteur</label><input id="author-name" name="author_name" class="form-control" maxlength="255" value="{{ old('author_name', $article->author_name) }}"></div>
            <div class="col-md-6">
                <label class="form-label" for="article-image">Image de l’article</label>
                <input id="article-image" type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                <small class="form-text">JPG, PNG ou WebP, 5 Mo maximum.</small>
                @if($article->image_path)
                    @php
                        $articleImageUrl = \Illuminate\Support\Str::startsWith($article->image_path, ['http://', 'https://'])
                            ? $article->image_path
                            : url('/storage/'.ltrim($article->image_path, '/'));
                    @endphp
                    <div class="mt-2 d-flex align-items-center gap-3">
                        <img src="{{ $articleImageUrl }}" alt="Image actuelle de l’article" style="width: 120px; aspect-ratio: 16 / 10; object-fit: cover;">
                        <label class="form-check-label"><input class="form-check-input me-1" type="checkbox" name="remove_image" value="1"> Retirer cette image</label>
                    </div>
                @endif
            </div>
            <div class="col-md-3"><label class="form-label" for="status">Statut</label><select id="status" name="status" class="form-select"><option value="draft" @selected(old('status', $article->status) === 'draft')>Brouillon</option><option value="published" @selected(old('status', $article->status) === 'published')>Publié</option></select></div>
            <div class="col-md-3"><label class="form-label" for="published-at">Date de publication</label><input id="published-at" type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', $article->published_at?->format('Y-m-d\\TH:i')) }}"></div>
            <div class="col-md-3"><label class="form-label" for="sort-order">Ordre</label><input id="sort-order" type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $article->sort_order) }}"></div>
            <div class="col-md-3 d-flex align-items-end"><div class="form-check mb-2"><input id="featured" class="form-check-input" type="checkbox" name="featured" value="1" @checked(old('featured', $article->featured))><label class="form-check-label" for="featured">Article à la une</label></div></div>
        </div>
        <div class="d-flex gap-2 mt-4"><x-admin.icon-action type="submit" variant="primary" icon="bi-floppy" label="Enregistrer l’article" /><x-admin.icon-action :href="route('admin.articles.index')" variant="outline-secondary" icon="bi-x-lg" label="Annuler et revenir aux articles" /></div>
    </form>
</div>
@endsection