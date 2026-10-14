@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Diaporama de l’accueil</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.home-slides.store') }}" method="POST" enctype="multipart/form-data" class="mb-4">
        @csrf
        <label for="photos" class="form-label">Ajouter une ou plusieurs photos</label>
        <div class="d-flex flex-wrap gap-2">
            <input id="photos" type="file" name="photos[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple required>
            <x-admin.icon-action type="submit" variant="primary" icon="bi-plus-lg" label="Ajouter au diaporama" />
        </div>
        <small class="text-muted">Formats acceptés : JPG, PNG, WebP. 10 Mo maximum par photo.</small>
    </form>

    @if($slides->isEmpty())
        <p class="text-muted">Aucune photo dans le diaporama pour le moment.</p>
    @else
        <x-admin.bulk-actions type="home-slides" :actions="['publish' => 'Publier', 'unpublish' => 'Masquer', 'delete' => 'Supprimer']" />
        <div class="row g-3">
            @foreach($slides as $slide)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="border rounded bg-white p-2 h-100">
                        <input type="checkbox" class="form-check-input mb-2" name="ids[]" value="{{ $slide->id }}" form="bulk-action-home-slides" data-bulk-item aria-label="Sélectionner {{ $slide->file_name }}">
                        <img src="{{ url('/storage/'.ltrim($slide->file_path, '/')) }}" alt="{{ $slide->alt_text ?: $slide->file_name }}" class="w-100 rounded mb-2" style="aspect-ratio: 4 / 3; object-fit: cover;">
                        <div class="small text-truncate mb-2" title="{{ $slide->file_name }}">{{ $slide->file_name }}</div>
                        <form action="{{ route('admin.home-slides.destroy', $slide) }}" method="POST" onsubmit="return confirm('Supprimer cette photo du diaporama ?')">
                            @csrf
                            @method('DELETE')
                            <x-admin.icon-action type="submit" size="sm" variant="outline-danger" icon="bi-trash" :label="'Supprimer '.$slide->file_name" />
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <x-admin.pagination :paginator="$slides" />
    @endif
</div>
@endsection