@extends('layouts.admin')

@section('content')
<div class="container py-4">
    @php
        $contentTypes = [
            'educations' => 'Formation',
            'certifications' => 'Certifications',
            'languages' => 'Langues',
            'technologies' => 'Technologies',
            'professional-references' => 'Références',
            'social-links' => 'Réseaux sociaux',
            'achievements' => 'Réalisations',
        ];
    @endphp
    <div class="mb-4">
        <h1 class="h3 mb-1">{{ $definition['title'] }}</h1>
        <p class="text-muted mb-0">Ajoutez, modifiez ou publiez les éléments de cette rubrique.</p>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <nav class="portfolio-content-tabs mb-4" aria-label="Rubriques de contenu">
        @foreach($contentTypes as $key => $label)
            <a href="{{ route('admin.portfolio-content.index', $key) }}" class="btn {{ $type === $key ? 'btn-primary' : 'btn-outline-secondary' }}" @if($type === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <a href="{{ route('admin.portfolio-content.export', $type) }}" class="btn btn-outline-primary">
            <i class="bi bi-download me-1" aria-hidden="true"></i>Exporter JSON
        </a>
        <form method="POST" action="{{ route('admin.portfolio-content.import', $type) }}" enctype="multipart/form-data" class="d-flex flex-wrap align-items-end gap-2">
            @csrf
            <div>
                <label class="form-label mb-1" for="portfolio-content-import">Importer dans « {{ $definition['title'] }} »</label>
                <input id="portfolio-content-import" class="form-control" type="file" name="import_file" accept=".json,application/json" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-upload me-1" aria-hidden="true"></i>Importer JSON
            </button>
        </form>
    </div>

    <section class="card shadow-sm mb-4">
        <div class="card-header">Ajouter : {{ $definition['singular'] }}</div>
        <div class="card-body">
            @include('admin.portfolio-content._form', ['item' => null])
        </div>
    </section>

    <section>
        <h2 class="h5 mb-3">Éléments enregistrés</h2>
        <x-admin.bulk-actions type="portfolio-content" :content-type="$type" :actions="['publish' => 'Publier / activer', 'unpublish' => 'Masquer / désactiver', 'delete' => 'Supprimer']" />
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr><th></th><th>Image</th><th>Nom</th><th>État</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr>
                            <td><input type="checkbox" class="form-check-input" name="ids[]" value="{{ $entry->id }}" form="bulk-action-portfolio-content-{{ \Illuminate\Support\Str::slug($type) }}" data-bulk-item aria-label="Sélectionner {{ $entryLabels[$entry->id] }}"></td>
                            <td>@if($entry->image_path)<img src="{{ asset('storage/'.$entry->image_path) }}" alt="" class="admin-content-thumbnail">@else<span class="text-muted">—</span>@endif</td>
                            <td>{{ $entryLabels[$entry->id] }}</td>
                            <td>
                                @php $isPublished = $type === 'social-links' ? $entry->is_active : $entry->published; @endphp
                                <span class="badge {{ $isPublished ? 'bg-success' : 'bg-secondary' }}">{{ $isPublished ? 'Publié' : 'Masqué' }}</span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <x-admin.icon-action :href="route('admin.portfolio-content.edit', ['type' => $type, 'entry' => $entry->id])" size="sm" variant="outline-primary" icon="bi-pencil" :label="'Modifier '.$entryLabels[$entry->id]" />
                                    <form method="POST" action="{{ route('admin.portfolio-content.destroy', ['type' => $type, 'entry' => $entry->id]) }}" onsubmit="return confirm('Supprimer cet élément ?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.icon-action type="submit" size="sm" variant="outline-danger" icon="bi-trash" :label="'Supprimer '.$entryLabels[$entry->id]" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Aucun élément enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.pagination :paginator="$entries" />
    </section>
</div>
@endsection