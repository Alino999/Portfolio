@extends('layouts.admin')

@section('content')
<div class="container py-4">
    @php
        $selectedTemplateId = old('resume_template_id', $selectedTemplateId ?? $templates->firstWhere('is_default', true)?->id);
        $selectedTemplate = $templates->firstWhere('id', (int) $selectedTemplateId);
        $availableLocales = $selectedTemplate?->languages ?: ['fr', 'en'];
        $availableFormats = $selectedTemplate?->available_formats ?: ['pdf', 'docx'];
    @endphp
    <style>
        .cv-template-choice { background: #fff; border: 1px solid #d7dee4; border-radius: 6px; cursor: pointer; display: block; height: 100%; padding: 10px; transition: border-color .15s, box-shadow .15s; }
        .cv-template-choice:hover, .cv-template-choice.is-selected { border-color: #147d75; box-shadow: 0 0 0 2px rgba(20, 125, 117, .12); }
        .cv-template-choice input { accent-color: #147d75; }
        .cv-template-preview { background: #f1f3f4; border: 1px solid #e3e7e9; height: 154px; margin: 8px 0 10px; overflow: hidden; padding: 13px; }
        .cv-template-preview__page { background: #fff; box-shadow: 0 2px 7px rgba(25, 42, 55, .13); height: 100%; padding: 12px 10px; }
        .cv-template-preview__name { background: #18354a; height: 8px; margin: 2px 0 7px; width: 62%; }
        .cv-template-preview__role { background: #16877e; height: 4px; margin-bottom: 10px; width: 42%; }
        .cv-template-preview__heading { border-bottom: 2px solid #16877e; height: 8px; margin: 8px 0 6px; width: 45%; }
        .cv-template-preview__line { background: #d7dfe2; height: 3px; margin: 4px 0; width: 88%; }
        .cv-template-preview__line.short { width: 60%; }
        .cv-template-preview--modern .cv-template-preview__page { border-left: 5px solid #16877e; padding-left: 8px; }
        .cv-template-preview--modern .cv-template-preview__name { background: #102a43; }
        .cv-template-preview--elegant .cv-template-preview__page { font-family: Georgia, serif; text-align: center; }
        .cv-template-preview--elegant .cv-template-preview__name { background: #512d3a; margin-left: auto; margin-right: auto; }
        .cv-template-preview--elegant .cv-template-preview__role { background: #b58a4a; margin-left: auto; margin-right: auto; }
        .cv-template-preview--elegant .cv-template-preview__heading { border-color: #b58a4a; margin-left: auto; margin-right: auto; }
        .cv-template-preview--creative .cv-template-preview__page { border-top: 25px solid #264653; position: relative; }
        .cv-template-preview--creative .cv-template-preview__name { background: #264653; margin-top: -20px; position: relative; width: 55%; }
        .cv-template-preview--creative .cv-template-preview__role, .cv-template-preview--creative .cv-template-preview__heading { background: #e76f51; border-color: #e76f51; }
        .cv-template-preview--editorial-nordic .cv-template-preview__page { border-top: 4px solid #b88755; font-family: Georgia, serif; }
        .cv-template-preview--portfolio-showcase .cv-template-preview__page { border-left: 7px solid #d97961; padding-left: 8px; }
        .cv-template-preview--bold-header .cv-template-preview__page { border-top: 16px solid #1f3a5f; padding-top: 8px; }
        .cv-template-preview--graduate-focus .cv-template-preview__page { border-bottom: 4px solid #b18b5e; font-family: Georgia, serif; text-align: center; }
        .cv-template-preview--graduate-focus .cv-template-preview__name, .cv-template-preview--graduate-focus .cv-template-preview__role, .cv-template-preview--graduate-focus .cv-template-preview__heading { margin-left: auto; margin-right: auto; }
        .cv-template-preview--portfolio-sidebar .cv-template-preview__page { border-left: 30px solid #26312d; background: #303734; padding-left: 8px; }
        .cv-template-preview--portfolio-sidebar .cv-template-preview__name { background: #fff; }
        .cv-template-preview--portfolio-sidebar .cv-template-preview__role, .cv-template-preview--portfolio-sidebar .cv-template-preview__heading { background: #7aa693; border-color: #7aa693; }
        .cv-template-preview--warm-academic .cv-template-preview__page { border: 1px solid #ead8d2; background: #fffaf2; font-family: Georgia, serif; }
        .cv-template-preview--warm-academic .cv-template-preview__role, .cv-template-preview--warm-academic .cv-template-preview__heading { background: #c77990; border-color: #c77990; }
        .cv-template-preview--portfolio-sidebar .cv-template-preview__page { background: #303734; border-left: 30px solid #26312d; padding-left: 8px; }
        .cv-template-preview--portfolio-sidebar .cv-template-preview__name { background: #fff; }
        .cv-template-preview--portfolio-sidebar .cv-template-preview__role,
        .cv-template-preview--portfolio-sidebar .cv-template-preview__heading { background: #7aa693; border-color: #7aa693; }
        .cv-template-preview--warm-academic .cv-template-preview__page { background: #fffaf2; border: 1px solid #ead8d2; font-family: Georgia, serif; }
        .cv-template-preview--warm-academic .cv-template-preview__role,
        .cv-template-preview--warm-academic .cv-template-preview__heading { background: #c77990; border-color: #c77990; }
        .cv-template-preview--portfolio-sidebar .cv-template-preview__page { background: #303734; border-left: 6px solid #7aa693; padding-left: 8px; }
        .cv-template-preview--portfolio-sidebar .cv-template-preview__name { background: #fff; }
        .cv-template-preview--portfolio-sidebar .cv-template-preview__role { background: #7aa693; }
        .cv-template-preview--portfolio-sidebar .cv-template-preview__line { background: #d4dfda; }
        .cv-template-preview--warm-academic .cv-template-preview__page { background: #fffaf2; border: 1px solid #ead8d2; font-family: Georgia, serif; }
        .cv-template-preview--warm-academic .cv-template-preview__role, .cv-template-preview--warm-academic .cv-template-preview__heading { background: #c77990; border-color: #c77990; }
        @media (prefers-reduced-motion: reduce) { .cv-template-choice { transition: none; } }
    </style>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>CV et modèles</h2>
        @role('super-admin')
            <x-admin.icon-action :href="route('admin.resume-templates.index')" variant="outline-secondary" icon="bi-collection" label="Bibliothèque de modèles" />
        @endrole
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-warning">{{ session('error') }}</div>@endif
    <div class="alert d-none" data-resume-feedback role="status" aria-live="polite"></div>

    @if($profile)
        <form id="resumeCreateForm" method="POST" action="{{ route('admin.resumes.store') }}" class="card card-body mb-4">
            @csrf
            <h5 class="mb-3">Créer un CV</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Titre du CV</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    @error('title')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Langue</label>
                    <select id="resumeLocale" name="locale" class="form-select">
                        @foreach(['fr' => 'Français', 'en' => 'English'] as $locale => $label)
                            @if(in_array($locale, $availableLocales, true))<option value="{{ $locale }}" {{ old('locale', 'fr') === $locale ? 'selected' : '' }}>{{ $label }}</option>@endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Longueur souhaitée</label>
                    <select id="resumeLength" name="length" class="form-select">
                        <option value="one_page">1 page</option>
                        <option value="two_pages">2 pages</option>
                        <option value="full">Complet</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Format</label>
                    <select id="resumeFormat" name="format" class="form-select">
                        @foreach(['pdf' => 'PDF', 'docx' => 'Word (DOCX)'] as $format => $label)
                            @if(in_array($format, $availableFormats, true))<option value="{{ $format }}" {{ old('format', 'pdf') === $format ? 'selected' : '' }}>{{ $label }}</option>@endif
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <fieldset class="border-0 p-0 m-0">
                        <legend class="form-label mb-2">Choisir un modèle</legend>
                        <div class="row g-3">
                            @foreach($templates as $template)
                                <div class="col-sm-6 col-xl-3">
                                    <label class="cv-template-choice {{ $selectedTemplateId == $template->id ? 'is-selected' : '' }}">
                                        <span class="d-flex align-items-center justify-content-between gap-2">
                                            <strong>{{ $template->name }}</strong>
                                            @php $templatePalette = $template->available_colors[0] ?? ['primary' => '#17324D', 'secondary' => '#0F766E']; @endphp
                                            <input type="radio" name="resume_template_id" value="{{ $template->id }}" data-formats="{{ implode(',', $template->available_formats ?? ['pdf', 'docx']) }}" data-locales="{{ implode(',', $template->languages ?? ['fr', 'en']) }}" data-recommended-pages="{{ $template->recommended_pages }}" data-primary-color="{{ $templatePalette['primary'] }}" data-secondary-color="{{ $templatePalette['secondary'] }}" data-photo-enabled="{{ $template->supportsPhoto() ? '1' : '0' }}" {{ $selectedTemplateId == $template->id ? 'checked' : '' }} required aria-label="Choisir le modèle {{ $template->name }}">
                                        </span>
                                        <span class="cv-template-preview cv-template-preview--{{ $template->layout }} d-block" aria-hidden="true">
                                            <span class="cv-template-preview__page d-block">
                                                <span class="cv-template-preview__name d-block"></span>
                                                <span class="cv-template-preview__role d-block"></span>
                                                <span class="cv-template-preview__heading d-block"></span>
                                                <span class="cv-template-preview__line d-block"></span>
                                                <span class="cv-template-preview__line short d-block"></span>
                                                <span class="cv-template-preview__heading d-block"></span>
                                                <span class="cv-template-preview__line d-block"></span>
                                                <span class="cv-template-preview__line short d-block"></span>
                                            </span>
                                        </span>
                                        <span class="small text-muted">{{ $template->description }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </fieldset>
                </div>
                <div class="col-12">
                    <label class="form-label">Sections à inclure</label>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach($sections as $key => $label)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="sections[]" value="{{ $key }}" id="section-{{ $key }}" {{ in_array($key, old('sections', array_keys($sections)), true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="section-{{ $key }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                @include('admin.resumes._options', ['resume' => null])
            </div>
            <x-admin.icon-action type="submit" variant="primary" icon="bi-file-earmark-plus" label="Créer le CV" class="align-self-start mt-3" />
        </form>
    @else
        <div class="alert alert-warning d-flex align-items-center justify-content-between gap-3"><span>Complétez votre profil avant de créer un CV.</span><x-admin.icon-action :href="route('admin.profile.edit')" size="sm" variant="outline-primary" icon="bi-person-gear" label="Modifier le profil" /></div>
    @endif

    <section class="admin-resume-list" aria-labelledby="resume-list-title">
        <div class="admin-resume-list-heading">
            <div>
                <span class="admin-profile-kicker">Bibliothèque</span>
                <h2 class="h5 mb-0" id="resume-list-title">CV enregistrés</h2>
            </div>
            <span class="small text-muted">{{ $resumes->total() }} CV</span>
        </div>
        <div class="mt-3">
            <x-admin.bulk-actions type="resumes" :actions="['publish' => 'Publier', 'unpublish' => 'Dépublier', 'delete' => 'Supprimer']" />
        </div>
        <div class="admin-resume-grid" data-resume-table-body>
            @forelse($resumes as $resume)
                <article class="admin-resume-card" data-resume-row="{{ $resume->id }}">
                    <input type="checkbox" class="form-check-input mb-2" name="ids[]" value="{{ $resume->id }}" form="bulk-action-resumes" data-bulk-item aria-label="Sélectionner le CV {{ $resume->title }}">
                    <div class="admin-resume-card-heading">
                        <div class="min-w-0">
                            <h3 class="h6 mb-1" data-resume-cell="title">{{ $resume->title }}</h3>
                            <span class="small text-muted">Mis à jour le {{ $resume->updated_at?->format('d/m/Y') }}</span>
                        </div>
                        <span class="badge {{ $resume->published ? 'text-bg-success' : 'text-bg-secondary' }}" data-resume-cell="status">{{ $resume->published ? 'Publié' : 'Brouillon' }}</span>
                    </div>
                    <div class="admin-resume-meta">
                        <span><small>Langue</small><strong data-resume-cell="locale">{{ strtoupper($resume->locale) }}</strong></span>
                        <span><small>Modèle</small><strong data-resume-cell="template">{{ $resume->template?->name ?? '—' }}</strong></span>
                        <span><small>Format</small><strong data-resume-cell="format">{{ strtoupper($resume->format) }}</strong></span>
                        <span><small>Longueur</small><strong>{{ ['one_page' => '1 page', 'two_pages' => '2 pages', 'full' => 'Complet'][$resume->length] ?? '—' }}</strong></span>
                    </div>
                    <div class="admin-resume-actions">
                        <x-admin.icon-action type="button" size="sm" variant="outline-secondary" icon="bi-eye" label="Aperçu du CV" data-bs-toggle="modal" data-bs-target="#resumePreviewModal" :data-resume-preview-url="route('admin.resumes.show', ['resume' => $resume, 'modal' => 1])" />
                        <x-admin.icon-action type="button" size="sm" variant="outline-primary" icon="bi-pencil" label="Modifier le CV" data-bs-toggle="modal" data-bs-target="#resumeEditModal" :data-resume-edit-url="route('admin.resumes.edit-modal', $resume)" />
                        <x-admin.icon-action :href="route('admin.resumes.download', $resume)" size="sm" variant="outline-success" icon="bi-download" label="Télécharger le CV" />
                        <x-admin.icon-action type="button" size="sm" variant="outline-danger" icon="bi-trash" label="Supprimer le CV" data-bs-toggle="modal" data-bs-target="#resumeDeleteModal" :data-resume-delete-url="route('admin.resumes.destroy', $resume)" :data-resume-delete-id="$resume->id" :data-resume-delete-title="$resume->title" />
                    </div>
                </article>
            @empty
                <p class="admin-resume-empty mb-0">Aucun CV créé.</p>
            @endforelse
        </div>
        <x-admin.pagination :paginator="$resumes" />
    </div>

    <div class="modal fade" id="resumePreviewModal" tabindex="-1" aria-labelledby="resumePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="resumePreviewModalLabel">Aperçu du CV</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe title="Aperçu du CV" data-resume-preview-frame src="about:blank" sandbox style="border: 0; display: block; height: 75vh; width: 100%;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resumeEditModal" tabindex="-1" aria-labelledby="resumeEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="resumeEditModalLabel">Modifier le CV</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body" data-resume-edit-body>
                    <div class="text-center text-muted py-5" role="status">Sélectionne un CV pour le modifier.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resumeDeleteModal" tabindex="-1" aria-labelledby="resumeDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="resumeDeleteModalLabel">Supprimer le CV</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p>Confirmer la suppression de <strong data-resume-delete-name></strong> ?</p>
                    <p class="text-muted mb-0">Cette action est définitive.</p>
                </div>
                <div class="modal-footer">
                    <x-admin.icon-action type="button" variant="outline-secondary" icon="bi-x-lg" label="Annuler la suppression" data-bs-dismiss="modal" />
                    <form method="POST" action="#" data-resume-delete-form>
                        @csrf
                        @method('DELETE')
                        <x-admin.icon-action type="submit" variant="danger" icon="bi-trash" label="Confirmer la suppression du CV" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const previewModal = document.getElementById('resumePreviewModal');
    const previewFrame = previewModal.querySelector('[data-resume-preview-frame]');
    const editModal = document.getElementById('resumeEditModal');
    const editBody = editModal.querySelector('[data-resume-edit-body]');
    const deleteModal = document.getElementById('resumeDeleteModal');
    const deleteForm = deleteModal.querySelector('[data-resume-delete-form]');
    const feedback = document.querySelector('[data-resume-feedback]');

    const showFeedback = (message, type = 'success') => {
        feedback.className = `alert alert-${type}`;
        feedback.textContent = message;
        feedback.hidden = false;
    };

    previewModal.addEventListener('show.bs.modal', (event) => {
        previewFrame.src = event.relatedTarget.dataset.resumePreviewUrl;
    });
    previewModal.addEventListener('hidden.bs.modal', () => {
        previewFrame.src = 'about:blank';
    });

    editModal.addEventListener('show.bs.modal', async (event) => {
        editBody.innerHTML = '<div class="text-center text-muted py-5" role="status">Chargement du formulaire…</div>';

        try {
            const response = await fetch(event.relatedTarget.dataset.resumeEditUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) throw new Error('Impossible de charger le formulaire de modification.');
            editBody.innerHTML = await response.text();
            editBody.querySelector('[name="title"]')?.focus();
        } catch (error) {
            editBody.innerHTML = '';
            const message = document.createElement('div');
            message.className = 'alert alert-danger';
            message.textContent = error.message;
            editBody.append(message);
        }
    });

    editBody.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-resume-edit-form]');
        if (!form) return;
        event.preventDefault();

        const submitButton = form.querySelector('[type="submit"]');
        const errors = form.querySelector('[data-form-errors]');
        submitButton.disabled = true;
        errors.classList.add('d-none');
        errors.textContent = '';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const result = await response.json();

            if (response.status === 422) {
                const list = document.createElement('ul');
                list.className = 'mb-0';
                Object.values(result.errors).flat().forEach((text) => {
                    const item = document.createElement('li');
                    item.textContent = text;
                    list.append(item);
                });
                errors.append(list);
                errors.classList.remove('d-none');
                return;
            }
            if (!response.ok) throw new Error(result.message || 'La modification a échoué.');

            const row = document.querySelector(`[data-resume-row="${result.resume.id}"]`);
            row.querySelector('[data-resume-cell="title"]').textContent = result.resume.title;
            row.querySelector('[data-resume-cell="locale"]').textContent = result.resume.locale.toUpperCase();
            row.querySelector('[data-resume-cell="template"]').textContent = result.resume.template || '';
            row.querySelector('[data-resume-cell="format"]').textContent = result.resume.format.toUpperCase();
            row.querySelector('[data-resume-delete-title]').dataset.resumeDeleteTitle = result.resume.title;
            bootstrap.Modal.getOrCreateInstance(editModal).hide();
            showFeedback(result.message);
        } catch (error) {
            errors.textContent = error.message;
            errors.classList.remove('d-none');
        } finally {
            submitButton.disabled = false;
        }
    });

    deleteModal.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        deleteForm.action = button.dataset.resumeDeleteUrl;
        deleteForm.dataset.resumeRowId = button.dataset.resumeDeleteId;
        deleteModal.querySelector('[data-resume-delete-name]').textContent = button.dataset.resumeDeleteTitle;
    });

    deleteForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = deleteForm.querySelector('[type="submit"]');
        button.disabled = true;

        try {
            const response = await fetch(deleteForm.action, {
                method: 'POST',
                body: new FormData(deleteForm),
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'La suppression a échoué.');

            document.querySelector(`[data-resume-row="${deleteForm.dataset.resumeRowId}"]`)?.remove();
            if (!document.querySelector('[data-resume-row]')) {
                document.querySelector('[data-resume-table-body]').innerHTML = '<p class="admin-resume-empty mb-0">Aucun CV créé.</p>';
            }
            bootstrap.Modal.getOrCreateInstance(deleteModal).hide();
            showFeedback(result.message);
        } catch (error) {
            bootstrap.Modal.getOrCreateInstance(deleteModal).hide();
            showFeedback(error.message, 'danger');
        } finally {
            button.disabled = false;
        }
    });

    const createForm = document.getElementById('resumeCreateForm');
    if (createForm) {
        const localeField = createForm.querySelector('#resumeLocale');
        const formatField = createForm.querySelector('#resumeFormat');
        const lengthField = createForm.querySelector('#resumeLength');
        const primaryColorField = createForm.querySelector('[name="customization[primary_color]"]');
        const secondaryColorField = createForm.querySelector('[name="customization[secondary_color]"]');
        const customColorsField = createForm.querySelector('[name="customization[use_custom_colors]"]');
        const paletteFields = [...createForm.querySelectorAll('[name="customization[palette]"]')];
        const photoToggle = createForm.querySelector('[name="customization[include_photo]"]');

        const updateOptions = (field, values, labels, preferred) => {
            const currentValue = field.value;
            field.replaceChildren(...values.map((value) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = labels[value];
                return option;
            }));
            field.value = values.includes(currentValue) ? currentValue : (values.includes(preferred) ? preferred : values[0]);
        };

        const applyPalette = (radio) => {
            if (!radio || customColorsField.checked) return;
            primaryColorField.value = radio.dataset.primaryColor;
            secondaryColorField.value = radio.dataset.secondaryColor;
        };

        const syncTemplateOptions = (template, changingTemplate = false) => {
            if (!template) return;
            const languages = template.dataset.locales.split(',');
            const formats = template.dataset.formats.split(',');
            const photoEnabled = template.dataset.photoEnabled === '1';
            updateOptions(localeField, languages, { fr: 'Français', en: 'English' }, 'fr');
            updateOptions(formatField, formats, { pdf: 'PDF', docx: 'Word (DOCX)' }, 'pdf');
            photoToggle.disabled = !photoEnabled;
            if (changingTemplate) photoToggle.checked = photoEnabled;
            if (changingTemplate && [...lengthField.options].some((option) => option.value === template.dataset.recommendedPages)) {
                lengthField.value = template.dataset.recommendedPages;
            }
            if (changingTemplate && !customColorsField.checked) {
                const matchingPalette = paletteFields.find((radio) => radio.dataset.primaryColor === template.dataset.primaryColor && radio.dataset.secondaryColor === template.dataset.secondaryColor)
                    ?? paletteFields.find((radio) => radio.value === 'custom');
                if (matchingPalette) matchingPalette.checked = true;
                applyPalette(matchingPalette);
            }
        };

        createForm.querySelectorAll('[name="resume_template_id"]').forEach((radio) => radio.addEventListener('change', () => syncTemplateOptions(radio, true)));
        paletteFields.forEach((radio) => radio.addEventListener('change', () => applyPalette(radio)));
        customColorsField.addEventListener('change', () => {
            if (!customColorsField.checked) applyPalette(paletteFields.find((radio) => radio.checked));
        });
        syncTemplateOptions(createForm.querySelector('[name="resume_template_id"]:checked'));
    }
});
</script>
@endpush
