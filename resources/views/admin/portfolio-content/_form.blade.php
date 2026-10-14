@php
    $isEditing = $item !== null;
    $formAction = $isEditing
        ? route('admin.portfolio-content.update', ['type' => $type, 'entry' => $item->id])
        : route('admin.portfolio-content.store', $type);
    $isActive = $item
        ? ($type === 'social-links' ? $item->is_active : $item->published)
        : true;
@endphp
<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
    @csrf
    @if($isEditing)@method('PUT')@endif
    <div class="row g-3">
        @foreach($definition['fields'] as $field)
            @php $columnClass = !empty($field['wide']) ? 'col-12' : 'col-md-6'; @endphp
            @if($field['type'] === 'localized')
                @php
                    $localizedValues = $item?->{$field['key']} ?? [];
                    $localizedValues = is_array($localizedValues) ? $localizedValues : [];
                    $inputType = $field['key'] === 'description' ? 'textarea' : 'text';
                @endphp
                <div class="{{ $columnClass }}">
                    <div class="row g-3">
                        @foreach(['fr' => 'FR', 'en' => 'EN'] as $locale => $suffix)
                            <div class="col-md-6">
                                <label class="form-label">{{ $field['label'] }} ({{ $suffix }})</label>
                                @if($inputType === 'textarea')
                                    <textarea name="{{ $field['key'] }}_{{ $locale }}" class="form-control" rows="3">{{ old($field['key'].'_'.$locale, $localizedValues[$locale] ?? '') }}</textarea>
                                @else
                                    <input type="text" name="{{ $field['key'] }}_{{ $locale }}" class="form-control" value="{{ old($field['key'].'_'.$locale, $localizedValues[$locale] ?? '') }}" @required(!empty($field['required']) && $locale === 'fr')>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                @php
                    $value = old($field['key'], $item?->{$field['key']} ?? '');
                    if ($value instanceof \Illuminate\Support\Carbon) $value = $value->format('Y-m-d');
                @endphp
                <div class="{{ $columnClass }}">
                    <label class="form-label">{{ $field['label'] }}</label>
                    @if($field['type'] === 'textarea')
                        <textarea name="{{ $field['key'] }}" class="form-control" rows="3">{{ $value }}</textarea>
                    @else
                        <input type="{{ $field['type'] }}" name="{{ $field['key'] }}" class="form-control" value="{{ $value }}" @required(!empty($field['required']) && $field['type'] !== 'checkbox')>
                    @endif
                </div>
            @endif
        @endforeach
        <div class="col-md-6">
            <label class="form-label" for="portfolio-entry-image">Image (facultative)</label>
            <input id="portfolio-entry-image" type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
            @if($item?->image_path)
                <div class="d-flex align-items-center gap-3 mt-2">
                    <img src="{{ asset('storage/'.$item->image_path) }}" alt="Aperçu de l’image actuelle" class="admin-content-thumbnail">
                    <label class="form-check-label"><input type="checkbox" name="remove_image" value="1" class="form-check-input me-1">Retirer l’image</label>
                </div>
            @endif
        </div>
        <div class="col-md-3">
            <label class="form-label" for="portfolio-entry-order">Ordre</label>
            <input id="portfolio-entry-order" type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $item?->sort_order ?? 0) }}">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <label class="form-check-label mb-2"><input type="checkbox" name="published" value="1" class="form-check-input me-1" @checked(old('published', $isActive))>{{ $type === 'social-links' ? 'Actif' : 'Publier' }}</label>
        </div>
    </div>
    <x-admin.icon-action type="submit" variant="primary" :icon="$isEditing ? 'bi-floppy' : 'bi-plus-lg'" :label="$isEditing ? 'Enregistrer les modifications' : 'Ajouter'" class="mt-4" />
</form>