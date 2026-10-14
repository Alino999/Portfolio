@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Modifier la compétence</h2>
        <x-admin.icon-action :href="route('admin.skills.index')" icon="bi-arrow-left" label="Retour aux compétences" variant="outline-secondary" />
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.skills.update', $skill) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nom FR</label>
                        <input type="text" name="name_fr" class="form-control" value="{{ App\Support\LocaleHelper::text($skill->name, 'fr') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nom EN</label>
                        <input type="text" name="name_en" class="form-control" value="{{ App\Support\LocaleHelper::text($skill->name, 'en') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Niveau (FR)</label>
                        <input type="text" name="level" class="form-control" value="{{ $skill->level }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Level (EN)</label>
                        <input type="text" name="level_en" class="form-control" value="{{ $skill->level_en }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pourcentage</label>
                        <input type="number" name="percentage" min="0" max="100" class="form-control" value="{{ $skill->percentage ?? 80 }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Années</label>
                        <input type="number" name="years_experience" min="0" class="form-control" value="{{ $skill->years_experience ?? 1 }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ordre</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ $skill->sort_order ?? 0 }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Visible</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="published" value="1" {{ $skill->published ? 'checked' : '' }}>
                            <label class="form-check-label">Publier</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Photo (facultative)</label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        @if($skill->image_path)
                            <div class="d-flex align-items-center gap-3 mt-2">
                                <img src="{{ asset('storage/'.$skill->image_path) }}" alt="Photo actuelle de {{ App\Support\LocaleHelper::text($skill->name) }}" style="width: 96px; height: 64px; object-fit: cover; border-radius: 6px;">
                                <label class="form-check-label"><input class="form-check-input me-1" type="checkbox" name="remove_image" value="1"> Retirer la photo</label>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description FR</label>
                        <textarea name="description_fr" class="form-control" rows="3">{{ App\Support\LocaleHelper::text($skill->description ?? ['fr' => '', 'en' => ''], 'fr') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Description EN</label>
                        <textarea name="description_en" class="form-control" rows="3">{{ App\Support\LocaleHelper::text($skill->description ?? ['fr' => '', 'en' => ''], 'en') }}</textarea>
                    </div>
                </div>
                <x-admin.icon-action type="submit" variant="primary" icon="bi-floppy" label="Enregistrer les modifications" class="mt-4" />
            </form>
        </div>
    </div>
</div>
@endsection
