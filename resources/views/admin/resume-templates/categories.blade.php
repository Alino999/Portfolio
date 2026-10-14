@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0">Catégories de modèles</h1>
        <x-admin.icon-action :href="route('admin.resume-templates.index')" icon="bi-arrow-left" label="Retour à la bibliothèque" variant="outline-secondary" />
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <form method="POST" action="{{ route('admin.resume-template-categories.store') }}" class="card card-body mb-4">
        @csrf
        <h2 class="h5">Ajouter une catégorie</h2>
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Nom</label><input name="name" class="form-control" required maxlength="120"></div>
            <div class="col-md-3"><label class="form-label">Slug</label><input name="slug" class="form-control" required pattern="[A-Za-z0-9_-]+"></div>
            <div class="col-md-4"><label class="form-label">Description</label><input name="description" class="form-control"></div>
            <div class="col-md-1"><label class="form-label">Ordre</label><input type="number" min="0" name="sort_order" class="form-control" value="0"></div>
            <div class="col-md-1 d-flex align-items-end"><x-admin.icon-action type="submit" variant="primary" icon="bi-plus-lg" label="Ajouter la catégorie" /></div>
        </div>
    </form>

    <x-admin.bulk-actions type="resume-template-categories" :actions="['publish' => 'Activer', 'unpublish' => 'Désactiver', 'delete' => 'Supprimer']" />
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead><tr><th>Nom</th><th>Slug</th><th>Description</th><th>Modèles</th><th>Ordre</th><th>Active</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td colspan="7">
                            <input type="checkbox" class="form-check-input me-2" name="ids[]" value="{{ $category->id }}" form="bulk-action-resume-template-categories" data-bulk-item aria-label="Sélectionner la catégorie {{ $category->name }}">
                            <form method="POST" action="{{ route('admin.resume-template-categories.update', $category) }}" class="row g-2 align-items-center">
                                @csrf @method('PUT')
                                <div class="col-md-2"><input name="name" value="{{ $category->name }}" class="form-control" required></div>
                                <div class="col-md-2"><input name="slug" value="{{ $category->slug }}" class="form-control" required></div>
                                <div class="col-md-3"><input name="description" value="{{ $category->description }}" class="form-control"></div>
                                <div class="col-md-1 text-center">{{ $category->templates_count }}</div>
                                <div class="col-md-1"><input type="number" min="0" name="sort_order" value="{{ $category->sort_order }}" class="form-control"></div>
                                <div class="col-md-1"><input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $category->is_active ? 'checked' : '' }} aria-label="Catégorie active"></div>
                                <div class="col-md-2 d-flex gap-2">
                                    <x-admin.icon-action type="submit" size="sm" variant="outline-primary" icon="bi-floppy" label="Enregistrer la catégorie" />
                                    <x-admin.icon-action type="submit" size="sm" variant="outline-danger" icon="bi-trash" label="Supprimer la catégorie" :form="'delete-category-'.$category->id" />
                                </div>
                            </form>
                            <form id="delete-category-{{ $category->id }}" method="POST" action="{{ route('admin.resume-template-categories.destroy', $category) }}" onsubmit="return confirm('Supprimer cette catégorie ?')">
                                @csrf @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Aucune catégorie définie.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-admin.pagination :paginator="$categories" />
</div>
@endsection
