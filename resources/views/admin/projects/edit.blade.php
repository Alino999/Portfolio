@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Modifier le projet</h2>
        <x-admin.icon-action :href="route('admin.projects.index')" icon="bi-arrow-left" label="Retour aux projets" variant="outline-secondary" />
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.projects.update', $project) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Titre FR</label>
                        <input type="text" name="title_fr" class="form-control" value="{{ App\Support\LocaleHelper::text($project->title, 'fr') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Titre EN</label>
                        <input type="text" name="title_en" class="form-control" value="{{ App\Support\LocaleHelper::text($project->title, 'en') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Catégorie</label>
                        <select name="project_category_id" class="form-select">
                            <option value="">Aucune</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $project->project_category_id == $category->id ? 'selected' : '' }}>
                                    {{ App\Support\LocaleHelper::text($category->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Client</label>
                        <input type="text" name="client" class="form-control" value="{{ $project->client }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Rôle</label>
                        <input type="text" name="role" class="form-control" value="{{ $project->role }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Description courte FR</label>
                        <textarea name="short_description_fr" class="form-control" rows="3">{{ App\Support\LocaleHelper::text($project->short_description ?? ['fr' => '', 'en' => ''], 'fr') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description courte EN</label>
                        <textarea name="short_description_en" class="form-control" rows="3">{{ App\Support\LocaleHelper::text($project->short_description ?? ['fr' => '', 'en' => ''], 'en') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Description FR</label>
                        <textarea name="description_fr" class="form-control" rows="4">{{ App\Support\LocaleHelper::text($project->description ?? ['fr' => '', 'en' => ''], 'fr') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description EN</label>
                        <textarea name="description_en" class="form-control" rows="4">{{ App\Support\LocaleHelper::text($project->description ?? ['fr' => '', 'en' => ''], 'en') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Importer une nouvelle image</label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        @if($project->image_path)
                            <div class="d-flex align-items-center gap-3 mt-2">
                                @if(!\Illuminate\Support\Str::startsWith($project->image_path, ['http://', 'https://']))
                                    <img src="{{ asset('storage/'.$project->image_path) }}" alt="" class="admin-content-thumbnail">
                                @endif
                                <label class="form-check-label"><input class="form-check-input me-1" type="checkbox" name="remove_image" value="1"> Retirer l’image</label>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">URL d’image externe</label>
                        <input type="url" name="image_path" class="form-control" value="{{ \Illuminate\Support\Str::startsWith($project->image_path ?? '', ['http://', 'https://']) ? $project->image_path : '' }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="project_date" class="form-control" value="{{ $project->project_date?->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="project_type" class="form-select">
                            <option value="professional" {{ $project->project_type === 'professional' ? 'selected' : '' }}>Professionnel</option>
                            <option value="personal" {{ $project->project_type === 'personal' ? 'selected' : '' }}>Personnel</option>
                            <option value="open_source" {{ $project->project_type === 'open_source' ? 'selected' : '' }}>Open source</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Statut</label>
                        <select name="status" class="form-select">
                            <option value="published" {{ $project->status === 'published' ? 'selected' : '' }}>Publié</option>
                            <option value="draft" {{ $project->status === 'draft' ? 'selected' : '' }}>Brouillon</option>
                            <option value="archived" {{ $project->status === 'archived' ? 'selected' : '' }}>Archivé</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ordre</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ $project->sort_order ?? 0 }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">URL de démonstration</label>
                        <input type="url" name="demo_url" class="form-control" value="{{ $project->demo_url }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">GitHub</label>
                        <input type="url" name="github_url" class="form-control" value="{{ $project->github_url }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Documentation</label>
                        <input type="url" name="documentation_url" class="form-control" value="{{ $project->documentation_url }}">
                    </div>

                    <div class="col-md-12">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="published" value="1" {{ $project->published ? 'checked' : '' }}>
                            <label class="form-check-label">Publier</label>
                        </div>
                    </div>
                </div>
                <x-admin.icon-action type="submit" variant="primary" icon="bi-floppy" label="Enregistrer les modifications" class="mt-4" />
            </form>
        </div>
    </div>
</div>
@endsection
