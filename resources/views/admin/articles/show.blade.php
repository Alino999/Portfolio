@extends('layouts.admin')

@section('content')
<div class="container py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <x-admin.icon-action :href="route('admin.articles.index')" size="sm" variant="outline-secondary" icon="bi-arrow-left" label="Retour au blogue" />
            <h1 class="h3 mt-2">{{ App\Support\LocaleHelper::text($article->title) }}</h1>
            <div class="text-muted">{{ $article->category ? App\Support\LocaleHelper::text($article->category->name) : 'Sans catégorie' }} · {{ $article->status === 'published' ? 'Publié' : 'Brouillon' }}</div>
        </div>
        <x-admin.icon-action :href="route('admin.articles.edit', $article)" variant="primary" icon="bi-pencil" label="Modifier l’article" />
    </div>

    @if($article->source_url)
        <p><x-admin.icon-action :href="$article->source_url" target="_blank" rel="noopener noreferrer" size="sm" variant="outline-secondary" icon="bi-box-arrow-up-right" label="Ouvrir la source originale" /></p>
    @endif

    @if($article->image_path)
        @php
            $articleImageUrl = \Illuminate\Support\Str::startsWith($article->image_path, ['http://', 'https://'])
                ? $article->image_path
                : url('/storage/'.ltrim($article->image_path, '/'));
        @endphp
        <img src="{{ $articleImageUrl }}" alt="Image de {{ App\Support\LocaleHelper::text($article->title) }}" class="img-fluid rounded mb-4" style="max-height: 420px; object-fit: cover;">
    @endif

    <div class="row g-3">
        @foreach(['fr' => 'Français', 'en' => 'English'] as $locale => $label)
            <div class="col-lg-6">
                <section class="border rounded bg-white p-3 h-100">
                    <h2 class="h5">{{ $label }}</h2>
                    <h3 class="h6">Résumé</h3>
                    <p class="text-muted" style="white-space: pre-line">{{ $article->excerpt[$locale] ?? '—' }}</p>
                    <h3 class="h6 mt-4">Contenu</h3>
                    <div style="white-space: pre-line">{{ $article->content[$locale] ?? '—' }}</div>
                </section>
            </div>
        @endforeach
    </div>

    <section class="mt-5">
        <h2 class="h4 mb-3">Commentaires ({{ $comments->total() }})</h2>
        <x-admin.bulk-actions type="article-comments" context-name="article_id" :context="$article->id" :actions="['publish' => 'Approuver', 'unpublish' => 'Remettre en attente', 'delete' => 'Supprimer']" />
        @forelse($comments as $comment)
            <div class="border rounded bg-white p-3 mb-2">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <input type="checkbox" class="form-check-input me-2" name="ids[]" value="{{ $comment->id }}" form="bulk-action-article-comments" data-bulk-item aria-label="Sélectionner le commentaire de {{ $comment->author_name }}">
                        <strong>{{ $comment->author_name }}</strong>
                        <span class="badge {{ $comment->status === 'approved' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $comment->status === 'approved' ? 'Publié' : 'À modérer' }}</span>
                        <p class="mb-0 mt-2" style="white-space: pre-line">{{ $comment->content }}</p>
                    </div>
                    <div class="d-flex gap-2 align-items-start">
                        @if($comment->status !== 'approved')
                            <form method="POST" action="{{ route('admin.articles.comments.approve', [$article, $comment]) }}">
                                @csrf
                                @method('PATCH')
                                <x-admin.icon-action type="submit" size="sm" variant="outline-success" icon="bi-check-lg" label="Approuver le commentaire" />
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.articles.comments.destroy', [$article, $comment]) }}" onsubmit="return confirm('Supprimer ce commentaire ?')">
                            @csrf
                            @method('DELETE')
                            <x-admin.icon-action type="submit" size="sm" variant="outline-danger" icon="bi-trash" label="Supprimer le commentaire" />
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">Aucun commentaire.</p>
        @endforelse
        <x-admin.pagination :paginator="$comments" />
    </section>
</div>
@endsection