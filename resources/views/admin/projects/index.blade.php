@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="mb-0">Projets</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header">Ajouter un projet</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.projects.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Titre FR</label>
                        <input type="text" name="title_fr" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Titre EN</label>
                        <input type="text" name="title_en" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Catégorie</label>
                        <select name="project_category_id" class="form-select">
                            <option value="">Aucune</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ App\Support\LocaleHelper::text($category->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Client</label>
                        <input type="text" name="client" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Rôle</label>
                        <input type="text" name="role" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Description courte FR</label>
                        <textarea name="short_description_fr" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description courte EN</label>
                        <textarea name="short_description_en" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Description FR</label>
                        <textarea name="description_fr" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description EN</label>
                        <textarea name="description_en" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Image du projet (facultative)</label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">URL d’image externe (facultative)</label>
                        <input type="url" name="image_path" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="project_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="project_type" class="form-select">
                            <option value="professional">Professionnel</option>
                            <option value="personal">Personnel</option>
                            <option value="open_source">Open source</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Statut</label>
                        <select name="status" class="form-select">
                            <option value="published">Publié</option>
                            <option value="draft">Brouillon</option>
                            <option value="archived">Archivé</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ordre</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">URL de démonstration</label>
                        <input type="url" name="demo_url" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">GitHub</label>
                        <input type="url" name="github_url" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Documentation</label>
                        <input type="url" name="documentation_url" class="form-control">
                    </div>

                    <div class="col-md-12">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="published" value="1" checked>
                            <label class="form-check-label">Publier</label>
                        </div>
                    </div>
                </div>
                <x-admin.icon-action type="submit" variant="primary" icon="bi-plus-lg" label="Ajouter le projet" class="mt-3" />
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">Liste des projets</div>
        <div class="card-body pb-0">
            <x-admin.bulk-actions type="projects" :actions="['publish' => 'Publier', 'unpublish' => 'Masquer', 'delete' => 'Supprimer']" />
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th></th>
                        <th>Projet</th>
                        <th>Client</th>
                        <th>Statut</th>
                        <th>Visible</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr>
                            <td><input type="checkbox" class="form-check-input" name="ids[]" value="{{ $project->id }}" form="bulk-action-projects" data-bulk-item aria-label="Sélectionner {{ App\Support\LocaleHelper::text($project->title) }}"></td>
                            <td>{{ App\Support\LocaleHelper::text($project->title) }}</td>
                            <td>{{ $project->client ?? '-' }}</td>
                            <td>{{ $project->status ?? 'published' }}</td>
                            <td>
                                @if($project->published)
                                    <span class="badge bg-success">Oui</span>
                                @else
                                    <span class="badge bg-secondary">Non</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <x-admin.icon-action :href="route('admin.projects.edit', $project)" size="sm" variant="outline-primary" icon="bi-pencil" :label="'Modifier '.App\Support\LocaleHelper::text($project->title)" />
                                    <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('Supprimer ce projet ?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.icon-action type="submit" size="sm" variant="outline-danger" icon="bi-trash" :label="'Supprimer '.App\Support\LocaleHelper::text($project->title)" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Aucun projet pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.pagination :paginator="$projects" />
    </div>
</div>
@endsection
