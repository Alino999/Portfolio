@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="mb-0">Compétences</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header">Ajouter une compétence</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.skills.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nom FR</label>
                        <input type="text" name="name_fr" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nom EN</label>
                        <input type="text" name="name_en" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Niveau (FR)</label>
                        <input type="text" name="level" class="form-control" placeholder="Expert">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Level (EN)</label>
                        <input type="text" name="level_en" class="form-control" placeholder="Expert">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pourcentage</label>
                        <input type="number" name="percentage" min="0" max="100" class="form-control" value="80">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Années</label>
                        <input type="number" name="years_experience" min="0" class="form-control" value="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ordre</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Visible</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="published" value="1" checked>
                            <label class="form-check-label">Publier</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Photo (facultative)</label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description FR</label>
                        <textarea name="description_fr" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description EN</label>
                        <textarea name="description_en" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <x-admin.icon-action type="submit" variant="primary" icon="bi-plus-lg" label="Ajouter la compétence" class="mt-3" />
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">Liste des compétences</div>
        <div class="card-body pb-0">
            <x-admin.bulk-actions type="skills" :actions="['publish' => 'Publier', 'unpublish' => 'Masquer', 'delete' => 'Supprimer']" />
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nom</th>
                        <th>Niveau</th>
                        <th>Pourcentage</th>
                        <th>Visible</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($skills as $skill)
                        <tr>
                            <td><input type="checkbox" class="form-check-input" name="ids[]" value="{{ $skill->id }}" form="bulk-action-skills" data-bulk-item aria-label="Sélectionner {{ App\Support\LocaleHelper::text($skill->name) }}"></td>
                            <td>{{ App\Support\LocaleHelper::text($skill->name) }}</td>
                            <td>{{ App\Support\LocaleHelper::text(['fr' => $skill->level, 'en' => $skill->level_en]) ?: '-' }}</td>
                            <td>{{ $skill->percentage ?? '-' }}</td>
                            <td>
                                @if($skill->published)
                                    <span class="badge bg-success">Oui</span>
                                @else
                                    <span class="badge bg-secondary">Non</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <x-admin.icon-action :href="route('admin.skills.edit', $skill)" size="sm" variant="outline-primary" icon="bi-pencil" :label="'Modifier '.App\Support\LocaleHelper::text($skill->name)" />
                                    <form method="POST" action="{{ route('admin.skills.destroy', $skill) }}" onsubmit="return confirm('Supprimer cette compétence ?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.icon-action type="submit" size="sm" variant="outline-danger" icon="bi-trash" :label="'Supprimer '.App\Support\LocaleHelper::text($skill->name)" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Aucune compétence pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-admin.pagination :paginator="$skills" />
    </div>
</div>
@endsection
