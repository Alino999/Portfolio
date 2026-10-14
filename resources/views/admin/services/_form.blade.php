<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Titre FR</label>
            <input type="text" name="title_fr" class="form-control" value="{{ old('title_fr', App\Support\LocaleHelper::text($service?->title ?? [], 'fr')) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Titre EN</label>
            <input type="text" name="title_en" class="form-control" value="{{ old('title_en', App\Support\LocaleHelper::text($service?->title ?? [], 'en')) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Description FR</label>
            <textarea name="description_fr" class="form-control" rows="3">{{ old('description_fr', App\Support\LocaleHelper::text($service?->description ?? [], 'fr')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Description EN</label>
            <textarea name="description_en" class="form-control" rows="3">{{ old('description_en', App\Support\LocaleHelper::text($service?->description ?? [], 'en')) }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">Icône (nom ou caractère)</label>
            <input type="text" name="icon" class="form-control" value="{{ old('icon', $service?->icon) }}" maxlength="100">
        </div>
        <div class="col-md-6">
            <label class="form-label">Image (facultative)</label>
            <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
            @if($service?->image_path)
                <div class="d-flex align-items-center gap-3 mt-2">
                    <img src="{{ asset('storage/'.$service->image_path) }}" alt="" class="admin-content-thumbnail">
                    <label class="form-check-label"><input class="form-check-input me-1" type="checkbox" name="remove_image" value="1"> Retirer l’image</label>
                </div>
            @endif
        </div>
        <div class="col-md-4">
            <label class="form-label">Ordre</label>
            <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $service?->sort_order ?? 0) }}">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="published" value="1" {{ old('published', $service?->published ?? true) ? 'checked' : '' }}>
                <label class="form-check-label">Publier</label>
            </div>
        </div>
    </div>
    <x-admin.icon-action type="submit" variant="primary" :icon="$method === 'PUT' ? 'bi-floppy' : 'bi-plus-lg'" :label="$method === 'PUT' ? 'Enregistrer les modifications' : 'Ajouter le service'" class="mt-3" />
</form>
