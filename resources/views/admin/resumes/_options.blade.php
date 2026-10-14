@php
    $resume ??= null;
    $storedSelection = old('content_selection', $resume?->content_selection);
    $customizationValues = old('customization', $resume?->customization ?? []);
    $selectedTemplateId = old('resume_template_id', $resume?->resume_template_id ?? ($selectedTemplateId ?? null));
    $selectedTemplate = $templates->firstWhere('id', (int) $selectedTemplateId);
    $templatePalette = $selectedTemplate?->available_colors[0] ?? ['primary' => '#17324D', 'secondary' => '#0F766E'];
    $photoMedia ??= collect();
    $profilePhotoPath = $resume?->profile?->avatar_path ?? ($profile?->avatar_path ?? null);
    $defaultPhotoPath = $profilePhotoPath ?? $photoMedia->sortByDesc('sort_order')->first()?->file_path;
    $selectedPhotoMediaId = old('customization.photo_media_id', $customizationValues['photo_media_id'] ?? '');
    $selectedPhotoMedia = $photoMedia->firstWhere('id', (int) $selectedPhotoMediaId);
    $photoPreviewPath = $selectedPhotoMedia?->file_path ?? $defaultPhotoPath;
    $photoAvailable = $selectedTemplate?->supportsPhoto() ?? false;
    $defaultPhotoEnabled = ! $resume && filled($defaultPhotoPath) && $photoAvailable;
    $profilePhotoUrl = $photoPreviewPath ? '/storage/'.ltrim($photoPreviewPath, '/') : null;
    $defaultPaletteKey = collect($palettes)->search(fn ($palette) => ($palette['primary'] ?? null) === $templatePalette['primary'] && ($palette['secondary'] ?? null) === $templatePalette['secondary']) ?: 'custom';
    $paletteSelection = old('customization.palette', $customizationValues['palette'] ?? $defaultPaletteKey);
    if (! array_key_exists($paletteSelection, $palettes) && $paletteSelection !== 'custom') $paletteSelection = $defaultPaletteKey;
    $sectionLabels = [
        'experiences' => 'Expériences',
        'educations' => 'Formations',
        'skills' => 'Compétences',
        'projects' => 'Projets',
        'certifications' => 'Certifications',
        'languages' => 'Langues',
        'technologies' => 'Technologies',
        'services' => 'Services',
        'social_links' => 'Réseaux sociaux',
        'references' => 'Références',
    ];
    $formPrefix = $resume ? 'resume-'.$resume->id : 'new-resume';
@endphp
<input type="hidden" name="content_selection_present" value="1">
<div class="col-12">
    <fieldset class="border rounded p-3">
        <legend class="form-label fs-6">Informations à inclure</legend>
        <p class="small text-muted">Choisis précisément les éléments affichés. Les modifications des fiches sources seront reprises à chaque aperçu ou export.</p>
        <div class="row g-3">
            @foreach($contentOptions as $group => $items)
                @php
                    $selectedIds = old('content_selection.'.$group, $storedSelection[$group] ?? $items->pluck('id')->all());
                    $selectedIds = array_map('intval', $selectedIds ?? []);
                @endphp
                <div class="col-md-6 col-xl-4">
                    <details class="border rounded p-3 h-100" open>
                        <summary class="fw-semibold mb-2">{{ $sectionLabels[$group] ?? str($group)->title() }} <span class="text-muted fw-normal">({{ $items->count() }})</span></summary>
                        @forelse($items as $item)
                            @php
                                $value = match ($group) {
                                    'experiences' => $item->position,
                                    'educations' => $item->degree,
                                    'skills', 'technologies', 'languages' => $item->name,
                                    'projects', 'certifications', 'services' => $item->title,
                                    'social_links' => $item->label ?: $item->platform,
                                    'references' => $item->name,
                                    default => $item->name ?? $item->title ?? '',
                                };
                                $title = is_array($value) ? App\Support\LocaleHelper::text($value) : (string) $value;
                                $subtitle = match ($group) {
                                    'experiences' => $item->company,
                                    'educations' => $item->school,
                                    'projects', 'services' => $item->client ?? null,
                                    'certifications' => $item->issuer,
                                    'languages', 'skills' => $item->level,
                                    'references' => $item->company,
                                    default => null,
                                };
                                $inputId = $formPrefix.'-'.$group.'-'.$item->id;
                            @endphp
                            <div class="form-check py-1">
                                <input class="form-check-input" type="checkbox" name="content_selection[{{ $group }}][]" value="{{ $item->id }}" id="{{ $inputId }}" {{ in_array($item->id, $selectedIds, true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="{{ $inputId }}">
                                    <span class="d-block">{{ $title }}</span>
                                    @if($subtitle)<small class="text-muted">{{ $subtitle }}</small>@endif
                                </label>
                            </div>
                        @empty
                            <p class="small text-muted mb-0">Aucun élément disponible.</p>
                        @endforelse
                    </details>
                </div>
            @endforeach
        </div>
    </fieldset>
</div>

<div class="col-12">
    <fieldset class="border rounded p-3">
        <legend class="form-label fs-6">Cible et résumé</legend>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Intitulé ciblé</label>
                <input type="text" name="target_role" class="form-control" value="{{ old('target_role', $resume?->target_role) }}" placeholder="Consultant Sage X3">
            </div>
            <div class="col-md-4">
                <label class="form-label">Marché visé</label>
                <select name="target_market" class="form-select">
                    @foreach(['' => 'Non précisé', 'burkina_faso' => 'Burkina Faso', 'africa_fr' => 'Afrique francophone', 'africa_en' => 'Afrique anglophone', 'europe' => 'Europe', 'canada' => 'Canada', 'international' => 'International'] as $market => $label)
                        <option value="{{ $market }}" {{ old('target_market', $resume?->target_market) === $market ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Résumé pour ce CV</label>
                <textarea name="summary_override" class="form-control" rows="2" placeholder="Laisser vide pour reprendre le profil">{{ old('summary_override', $resume?->summary_override) }}</textarea>
            </div>
        </div>
    </fieldset>
</div>

<div class="col-12">
    <fieldset class="border rounded p-3">
        <legend class="form-label fs-6">Personnaliser le design</legend>
        <div class="d-flex flex-wrap align-items-center gap-3 mb-3" data-resume-photo-control>
            <img src="{{ $profilePhotoUrl ?? '' }}" alt="Aperçu de la photo sélectionnée" data-resume-photo-preview class="{{ $profilePhotoUrl ? '' : 'd-none' }}" style="width:64px;height:76px;object-fit:cover;border-radius:6px;">
            <div class="flex-grow-1">
                <label class="form-label" for="{{ $formPrefix }}-photo-source">Photo existante</label>
                <select id="{{ $formPrefix }}-photo-source" name="customization[photo_media_id]" class="form-select" data-resume-photo-select data-default-photo-url="{{ $defaultPhotoPath ? '/storage/'.ltrim($defaultPhotoPath, '/') : '' }}">
                    <option value="">{{ $profilePhotoPath ? 'Photo du profil' : 'Photo automatique' }}</option>
                    @foreach($photoMedia as $photo)
                        <option value="{{ $photo->id }}" data-photo-url="{{ '/storage/'.ltrim($photo->file_path, '/') }}" @selected((int) $selectedPhotoMediaId === $photo->id)>{{ $photo->title ?: $photo->file_name }}</option>
                    @endforeach
                </select>
                <small class="form-text">Choisis une photo JPG déjà présente dans le diaporama. Aucun fichier n’est importé.</small>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Palette</label>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($palettes as $key => $palette)
                        <label class="form-check d-flex align-items-center gap-2">
                            <input class="form-check-input mt-0" type="radio" name="customization[palette]" value="{{ $key }}" data-primary-color="{{ $palette['primary'] }}" data-secondary-color="{{ $palette['secondary'] }}" {{ $paletteSelection === $key ? 'checked' : '' }}>
                            <span class="d-inline-flex" aria-hidden="true"><span style="background:{{ $palette['primary'] }}; width:18px; height:18px;"></span><span style="background:{{ $palette['secondary'] }}; width:18px; height:18px;"></span></span>
                            <span>{{ $palette['name'] }}</span>
                        </label>
                    @endforeach
                    <label class="form-check d-flex align-items-center gap-2">
                        <input class="form-check-input mt-0" type="radio" name="customization[palette]" value="custom" data-primary-color="{{ $templatePalette['primary'] }}" data-secondary-color="{{ $templatePalette['secondary'] }}" {{ $paletteSelection === 'custom' ? 'checked' : '' }}>
                        <span class="d-inline-flex" aria-hidden="true"><span style="background:{{ $templatePalette['primary'] }}; width:18px; height:18px;"></span><span style="background:{{ $templatePalette['secondary'] }}; width:18px; height:18px;"></span></span>
                        <span>Couleurs du modèle</span>
                    </label>
                </div>
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="customization[use_custom_colors]" value="1" id="{{ $formPrefix }}-custom-colors" {{ old('customization.use_custom_colors', $customizationValues['use_custom_colors'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="{{ $formPrefix }}-custom-colors">Utiliser des couleurs personnalisées</label>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Couleur principale</label>
                <input type="color" name="customization[primary_color]" class="form-control form-control-color" value="{{ old('customization.primary_color', $customizationValues['primary_color'] ?? $templatePalette['primary']) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Couleur secondaire</label>
                <input type="color" name="customization[secondary_color]" class="form-control form-control-color" value="{{ old('customization.secondary_color', $customizationValues['secondary_color'] ?? $templatePalette['secondary']) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Police</label>
                <select name="customization[font]" class="form-select">
                    @foreach(['sans' => 'Sans serif', 'serif' => 'Serif', 'mono' => 'Monospace', 'Arial' => 'Arial', 'Calibri' => 'Calibri', 'Georgia' => 'Georgia', 'Aptos' => 'Aptos', 'Inter' => 'Inter'] as $font => $label)
                        <option value="{{ $font }}" {{ old('customization.font', $customizationValues['font'] ?? 'sans') === $font ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Taille du texte</label>
                <select name="customization[font_size]" class="form-select">
                    @foreach([8, 9, 10, 11, 12, 13, 14] as $fontSize)
                        <option value="{{ $fontSize }}" {{ (int) old('customization.font_size', $customizationValues['font_size'] ?? 10) === $fontSize ? 'selected' : '' }}>{{ $fontSize }} pt</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 d-flex flex-wrap gap-3">
                @foreach(['include_photo' => 'Photo', 'display_skills' => 'Compétences', 'display_projects' => 'Projets', 'display_certifications' => 'Certifications', 'display_references' => 'Références', 'display_social' => 'Réseaux sociaux'] as $key => $label)
                    @php
                        $toggleEnabled = old('customization.'.$key, $customizationValues[$key] ?? ($key === 'include_photo' ? $defaultPhotoEnabled : true));
                        if ($key === 'include_photo') $toggleEnabled = $photoAvailable && $toggleEnabled;
                    @endphp
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="customization[{{ $key }}]" value="1" id="{{ $formPrefix }}-{{ $key }}" {{ $toggleEnabled ? 'checked' : '' }} @if($key === 'include_photo' && ! $photoAvailable) disabled @endif>
                        <label class="form-check-label" for="{{ $formPrefix }}-{{ $key }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
        </div>
    </fieldset>
</div>
