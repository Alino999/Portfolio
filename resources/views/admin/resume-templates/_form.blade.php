@php
    $template ??= null;
    $selectedLanguages = old('languages', $template?->languages ?? ['fr', 'en']);
    $selectedFormats = old('available_formats', $template?->available_formats ?? ['pdf', 'docx']);
    $selectedBlocks = old('blocks', $template?->blocks ?? array_keys($blocks));
    $templateColors = $template?->available_colors ?? [];
@endphp
<form method="POST" action="{{ $action }}">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nom du modèle</label>
            <input name="name" class="form-control" value="{{ old('name', $template?->name) }}" required maxlength="120">
        </div>
        <div class="col-md-6">
            <label class="form-label">Identifiant URL</label>
            <input name="slug" class="form-control" value="{{ old('slug', $template?->slug) }}" required pattern="[A-Za-z0-9_-]+">
        </div>
        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="2">{{ old('description', $template?->description) }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">Catégorie</label>
            <select name="category_id" class="form-select" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ (string) old('category_id', $template?->category_id) === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Style</label>
            <input name="style" class="form-control" value="{{ old('style', $template?->style ?? 'professional') }}" maxlength="80" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Structure graphique</label>
            <select name="layout" class="form-select" required>
                @foreach($layouts as $layout)
                    <option value="{{ $layout }}" {{ old('layout', $template?->layout ?? 'classic-professional') === $layout ? 'selected' : '' }}>{{ str($layout)->replace('-', ' ')->title() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Pagination recommandée</label>
            <select name="recommended_pages" class="form-select">
                <option value="one_page" {{ old('recommended_pages', $template?->recommended_pages ?? 'two_pages') === 'one_page' ? 'selected' : '' }}>1 page</option>
                <option value="two_pages" {{ old('recommended_pages', $template?->recommended_pages ?? 'two_pages') === 'two_pages' ? 'selected' : '' }}>2 pages</option>
                <option value="full" {{ old('recommended_pages', $template?->recommended_pages ?? 'two_pages') === 'full' ? 'selected' : '' }}>Complet</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Ordre</label>
            <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $template?->sort_order ?? 0) }}">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="ats_friendly" value="1" {{ old('ats_friendly', $template?->ats_friendly ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">Compatible ATS</label>
            </div>
        </div>
        <div class="col-md-6">
            <fieldset class="border rounded p-3 h-100">
                <legend class="form-label fs-6">Langues disponibles</legend>
                @foreach(['fr' => 'Français', 'en' => 'English'] as $locale => $label)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="languages[]" value="{{ $locale }}" id="template-language-{{ $locale }}-{{ $template?->id ?? 'new' }}" {{ in_array($locale, $selectedLanguages, true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="template-language-{{ $locale }}-{{ $template?->id ?? 'new' }}">{{ $label }}</label>
                    </div>
                @endforeach
            </fieldset>
        </div>
        <div class="col-md-6">
            <fieldset class="border rounded p-3 h-100">
                <legend class="form-label fs-6">Formats disponibles</legend>
                @foreach(['pdf' => 'PDF', 'docx' => 'DOCX'] as $format => $label)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="available_formats[]" value="{{ $format }}" id="template-format-{{ $format }}-{{ $template?->id ?? 'new' }}" {{ in_array($format, $selectedFormats, true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="template-format-{{ $format }}-{{ $template?->id ?? 'new' }}">{{ $label }}</label>
                    </div>
                @endforeach
            </fieldset>
        </div>
        <div class="col-12">
            <fieldset class="border rounded p-3">
                <legend class="form-label fs-6">Palettes professionnelles</legend>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($palettes as $key => $palette)
                        @php
                            $isPaletteSelected = collect($templateColors)->contains(fn ($color) => ($color['primary'] ?? null) === $palette['primary']);
                            $paletteSelection = old('palettes', $template ? array_keys(array_filter($palettes, fn ($option) => collect($templateColors)->contains(fn ($color) => ($color['primary'] ?? null) === $option['primary']))) : ['blue_professional']);
                        @endphp
                        <label class="form-check d-flex align-items-center gap-2">
                            <input class="form-check-input mt-0" type="checkbox" name="palettes[]" value="{{ $key }}" {{ in_array($key, $paletteSelection, true) ? 'checked' : '' }}>
                            <span class="d-inline-flex" aria-hidden="true">
                                <span style="background:{{ $palette['primary'] }}; width:18px; height:18px;"></span>
                                <span style="background:{{ $palette['secondary'] }}; width:18px; height:18px;"></span>
                            </span>
                            <span>{{ $palette['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        </div>
        <div class="col-12">
            <fieldset class="border rounded p-3">
                <legend class="form-label fs-6">Blocs réutilisables</legend>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($blocks as $key => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="blocks[]" value="{{ $key }}" id="template-block-{{ $key }}-{{ $template?->id ?? 'new' }}" {{ in_array($key, $selectedBlocks, true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="template-block-{{ $key }}-{{ $template?->id ?? 'new' }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
            </fieldset>
        </div>
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $template?->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label">Modèle actif et sélectionnable</label>
            </div>
        </div>
    </div>
    <x-admin.icon-action type="submit" variant="primary" :icon="$method === 'PUT' ? 'bi-floppy' : 'bi-plus-lg'" :label="$method === 'PUT' ? 'Enregistrer le modèle' : 'Créer le modèle'" class="mt-3" />
</form>
