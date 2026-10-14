@php
    $description = $experience?->description ?? [];
    $descriptionFr = $description['fr'] ?? '';
    $descriptionEn = $description['en'] ?? $descriptionFr;
    $responsibilities = $experience?->responsibilities ?? [];
    $responsibilitiesFr = $responsibilities['fr'] ?? $responsibilities;
    $responsibilitiesEn = $responsibilities['en'] ?? $responsibilitiesFr;
    $achievements = $experience?->achievements ?? [];
    $achievementsFr = $achievements['fr'] ?? $achievements;
    $achievementsEn = $achievements['en'] ?? $achievementsFr;
    $descriptionFr = is_array($descriptionFr) ? implode("\n", $descriptionFr) : $descriptionFr;
    $descriptionEn = is_array($descriptionEn) ? implode("\n", $descriptionEn) : $descriptionEn;
    $responsibilitiesFr = is_array($responsibilitiesFr) ? implode("\n", $responsibilitiesFr) : $responsibilitiesFr;
    $responsibilitiesEn = is_array($responsibilitiesEn) ? implode("\n", $responsibilitiesEn) : $responsibilitiesEn;
    $achievementsFr = is_array($achievementsFr) ? implode("\n", $achievementsFr) : $achievementsFr;
    $achievementsEn = is_array($achievementsEn) ? implode("\n", $achievementsEn) : $achievementsEn;
@endphp
<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Entreprise (FR)</label>
            <input type="text" name="company" class="form-control @error('company') is-invalid @enderror" value="{{ old('company', $experience?->company) }}" required>
            @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Company (EN)</label>
            <input type="text" name="company_en" class="form-control" value="{{ old('company_en', $experience?->company_en) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Poste (FR)</label>
            <input type="text" name="position" class="form-control @error('position') is-invalid @enderror" value="{{ old('position', $experience?->position) }}" required>
            @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Position (EN)</label>
            <input type="text" name="position_en" class="form-control" value="{{ old('position_en', $experience?->position_en) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Lieu</label>
            <input type="text" name="location" class="form-control" value="{{ old('location', $experience?->location) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Type de contrat</label>
            <input type="text" name="contract_type" class="form-control" value="{{ old('contract_type', $experience?->contract_type) }}" placeholder="CDI, Freelance...">
        </div>
        <div class="col-md-4">
            <label class="form-label">Site web</label>
            <input type="url" name="website" class="form-control" value="{{ old('website', $experience?->website) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Photo de l’expérience (facultative)</label>
            <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
            @if($experience?->image_path)
                <div class="d-flex align-items-center gap-3 mt-2">
                    <img src="{{ asset('storage/'.$experience->image_path) }}" alt="" class="admin-content-thumbnail">
                    <label class="form-check-label"><input class="form-check-input me-1" type="checkbox" name="remove_image" value="1"> Retirer la photo</label>
                </div>
            @endif
        </div>
        <div class="col-md-6">
            <label class="form-label">Date de début</label>
            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $experience?->start_date?->format('Y-m-d')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Date de fin</label>
            <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $experience?->end_date?->format('Y-m-d')) }}">
        </div>
        <div class="col-md-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_current" value="1" {{ old('is_current', $experience?->is_current) ? 'checked' : '' }}>
                <label class="form-check-label">Poste actuel</label>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Description FR</label>
            <textarea name="description_fr" class="form-control" rows="3">{{ old('description_fr', $descriptionFr) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Description EN</label>
            <textarea name="description_en" class="form-control" rows="3">{{ old('description_en', $descriptionEn) }}</textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">Responsabilités FR</label>
            <textarea name="responsibilities_fr" class="form-control" rows="4" placeholder="Une responsabilité par ligne">{{ old('responsibilities_fr', $responsibilitiesFr) }}</textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">Responsabilités EN</label>
            <textarea name="responsibilities_en" class="form-control" rows="4" placeholder="One responsibility per line">{{ old('responsibilities_en', $responsibilitiesEn) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Réalisations FR</label>
            <textarea name="achievements_fr" class="form-control" rows="3" placeholder="Une réalisation par ligne">{{ old('achievements_fr', $achievementsFr) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Réalisations EN</label>
            <textarea name="achievements_en" class="form-control" rows="3" placeholder="One achievement per line">{{ old('achievements_en', $achievementsEn) }}</textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">Technologies</label>
            <textarea name="technologies" class="form-control" rows="2" placeholder="Une technologie par ligne">{{ old('technologies', implode("\n", $experience?->technologies ?? [])) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Ordre d’affichage</label>
            <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $experience?->sort_order ?? 0) }}">
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="published" value="1" {{ old('published', $experience?->published ?? true) ? 'checked' : '' }}>
                <label class="form-check-label">Publier cette expérience</label>
            </div>
        </div>
    </div>
    <x-admin.icon-action type="submit" variant="primary" :icon="$method === 'PUT' ? 'bi-floppy' : 'bi-plus-lg'" :label="$method === 'PUT' ? 'Enregistrer les modifications' : 'Ajouter l’expérience'" class="mt-4" />
</form>
