@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="mb-1">Gestion du blogue</h2>
            <p class="text-muted mb-0">Importez des sources, vérifiez les brouillons, puis publiez vos articles.</p>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-warning">{{ session('error') }}</div>@endif
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <section class="border rounded bg-white p-3 h-100">
                <h5>Recherche Google Actualités</h5>
                <form method="POST" action="{{ route('admin.articles.search') }}">
                    @csrf
                    <label class="form-label" for="search-category">Catégorie</label>
                    <select id="search-category" name="category_id" class="form-select mb-3" required>
                        <option value="">Choisir une catégorie</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ App\Support\LocaleHelper::text($category->name) }}</option>
                        @endforeach
                    </select>
                    <label class="form-label" for="search-query">Recherche complémentaire</label>
                    <input id="search-query" name="query" class="form-control mb-3" maxlength="160" placeholder="Ex. Sage X3 au Burkina Faso">
                    <input type="hidden" name="locale" value="{{ in_array(app()->getLocale(), ['fr', 'en'], true) ? app()->getLocale() : 'fr' }}">
                    <button type="submit" class="btn btn-primary w-100 mt-1" @disabled($categories->isEmpty())>
                        <i class="bi bi-search me-2" aria-hidden="true"></i>Rechercher et ajouter les brouillons
                    </button>
                </form>
                <small class="text-muted d-block mt-2">Jusqu’à 10 résultats sont enregistrés. Les doublons sont ignorés.</small>
            </section>
        </div>
        <div class="col-lg-4">
            <section class="border rounded bg-white p-3 h-100">
                <h5>Importer depuis un lien</h5>
                <form method="POST" action="{{ route('admin.articles.import-url') }}">
                    @csrf
                    <label class="form-label" for="source-url">Adresse de l’article</label>
                    <input id="source-url" type="url" name="source_url" class="form-control mb-3" placeholder="https://exemple.com/article" required>
                    <label class="form-label" for="import-category">Catégorie</label>
                    <select id="import-category" name="category_id" class="form-select mb-3" required>
                        <option value="">Choisir une catégorie</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ App\Support\LocaleHelper::text($category->name) }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="locale" value="{{ in_array(app()->getLocale(), ['fr', 'en'], true) ? app()->getLocale() : 'fr' }}">
                    <button type="submit" class="btn btn-outline-primary w-100 mt-1" @disabled($categories->isEmpty())>
                        <i class="bi bi-cloud-arrow-down me-2" aria-hidden="true"></i>Importer en brouillon
                    </button>
                </form>
                <small class="text-muted d-block mt-2">Seuls le titre, le résumé public et le lien source sont récupérés.</small>
            </section>
        </div>
        <div class="col-lg-4">
            <section class="border rounded bg-white p-3 h-100">
                <h5>Ajouter une catégorie</h5>
                <form method="POST" action="{{ route('admin.articles.categories.store') }}">
                    @csrf
                    <label class="form-label" for="category-fr">Nom français</label>
                    <input id="category-fr" name="name_fr" class="form-control mb-3" maxlength="255" required>
                    <label class="form-label" for="category-en">Name in English</label>
                    <input id="category-en" name="name_en" class="form-control mb-3" maxlength="255">
                    <x-admin.icon-action type="submit" variant="outline-secondary" icon="bi-tag" label="Enregistrer la catégorie" />
                </form>
                @if($categories->isNotEmpty())
                    <div class="small text-muted mt-3">
                        @foreach($categories as $category)
                            <span class="me-2">{{ App\Support\LocaleHelper::text($category->name) }}</span>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="h5 mb-0">Articles enregistrés</h3>
        <form method="GET" action="{{ route('admin.articles.index') }}" class="d-flex flex-wrap gap-2">
            <select name="category_id" class="form-select form-select-sm" aria-label="Filtrer par catégorie">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ App\Support\LocaleHelper::text($category->name) }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-sm" aria-label="Filtrer par statut">
                <option value="">Tous les statuts</option>
                <option value="draft" @selected(request('status') === 'draft')>Brouillons</option>
                <option value="published" @selected(request('status') === 'published')>Publiés</option>
            </select>
            <x-admin.icon-action type="submit" size="sm" variant="outline-secondary" icon="bi-funnel" label="Filtrer les articles" />
        </form>
    </div>

    <x-admin.bulk-actions type="articles" :actions="['publish' => 'Publier', 'unpublish' => 'Dépublier', 'delete' => 'Supprimer']" />

    <div class="table-responsive bg-white border rounded">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th></th><th>Article</th><th>Catégorie</th><th>Statut</th><th>Source</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse($articles as $article)
                    <tr>
                        <td><input type="checkbox" class="form-check-input" name="ids[]" value="{{ $article->id }}" form="bulk-action-articles" data-bulk-item aria-label="Sélectionner {{ App\Support\LocaleHelper::text($article->title) }}"></td>
                        <td>
                            <a class="fw-semibold" href="{{ route('admin.articles.show', $article) }}">{{ App\Support\LocaleHelper::text($article->title) }}</a>
                            <div class="small text-muted">{{ $article->created_at?->format('d/m/Y H:i') }}</div>
                        </td>
                        <td>{{ $article->category ? App\Support\LocaleHelper::text($article->category->name) : '—' }}</td>
                        <td><span class="badge {{ $article->status === 'published' ? 'bg-success' : 'bg-secondary' }}">{{ $article->status === 'published' ? 'Publié' : 'Brouillon' }}</span></td>
                        <td>@if($article->source_url)<x-admin.icon-action :href="$article->source_url" target="_blank" rel="noopener noreferrer" size="sm" variant="outline-secondary" icon="bi-box-arrow-up-right" label="Ouvrir la source de l’article" />@else<span class="text-muted">—</span>@endif</td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                <x-admin.icon-action :href="route('admin.articles.show', $article)" size="sm" variant="outline-secondary" icon="bi-eye" :label="'Voir '.App\Support\LocaleHelper::text($article->title)" />
                                <x-admin.icon-action :href="route('admin.articles.edit', $article)" size="sm" variant="outline-primary" icon="bi-pencil" :label="'Modifier '.App\Support\LocaleHelper::text($article->title)" />
                                <form method="POST" action="{{ route('admin.articles.toggle-publication', $article) }}">
                                    @csrf
                                    @method('PATCH')
                                    <x-admin.icon-action type="submit" size="sm" :variant="$article->status === 'published' ? 'warning' : 'success'" :icon="$article->status === 'published' ? 'bi-eye-slash' : 'bi-eye'" :label="$article->status === 'published' ? 'Dépublier l’article' : 'Publier l’article'" />
                                </form>
                                <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Supprimer cet article ?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-admin.icon-action type="submit" size="sm" variant="outline-danger" icon="bi-trash" label="Supprimer l’article" />
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun article enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-admin.pagination :paginator="$articles" />
</div>
@endsection