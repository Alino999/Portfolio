<form method="POST" action="{{ route('admin.resumes.update', $resume) }}" data-resume-edit-form>
    @csrf
    @method('PUT')
    <div class="alert alert-danger d-none" data-form-errors role="alert"></div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="resume-title-{{ $resume->id }}">Titre du CV</label>
            <input id="resume-title-{{ $resume->id }}" type="text" name="title" class="form-control" value="{{ $resume->title }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="resume-locale-{{ $resume->id }}">Langue</label>
            <select id="resume-locale-{{ $resume->id }}" name="locale" class="form-select">
                <option value="fr" {{ $resume->locale === 'fr' ? 'selected' : '' }}>Français</option>
                <option value="en" {{ $resume->locale === 'en' ? 'selected' : '' }}>English</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="resume-template-{{ $resume->id }}">Modèle</label>
            <select id="resume-template-{{ $resume->id }}" name="resume_template_id" class="form-select" data-resume-template-photo-select required>
                @foreach($templates as $template)
                    <option value="{{ $template->id }}" data-photo-enabled="{{ $template->supportsPhoto() ? '1' : '0' }}" {{ $resume->resume_template_id === $template->id ? 'selected' : '' }}>{{ $template->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="resume-length-{{ $resume->id }}">Longueur</label>
            <select id="resume-length-{{ $resume->id }}" name="length" class="form-select">
                <option value="one_page" {{ $resume->length === 'one_page' ? 'selected' : '' }}>1 page</option>
                <option value="two_pages" {{ $resume->length === 'two_pages' ? 'selected' : '' }}>2 pages</option>
                <option value="full" {{ $resume->length === 'full' ? 'selected' : '' }}>Complet</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="resume-format-{{ $resume->id }}">Format</label>
            <select id="resume-format-{{ $resume->id }}" name="format" class="form-select">
                <option value="pdf" {{ $resume->format === 'pdf' ? 'selected' : '' }}>PDF</option>
                <option value="docx" {{ $resume->format === 'docx' ? 'selected' : '' }}>Word (DOCX)</option>
            </select>
        </div>
        <div class="col-12">
            <fieldset class="border-0 p-0 m-0">
                <legend class="form-label">Sections à inclure</legend>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($sections as $key => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sections[]" value="{{ $key }}" id="edit-section-{{ $resume->id }}-{{ $key }}" {{ in_array($key, $resume->sections ?? [], true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="edit-section-{{ $resume->id }}-{{ $key }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
            </fieldset>
        </div>
        @include('admin.resumes._options', ['resume' => $resume])
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4">
        <x-admin.icon-action type="button" variant="outline-secondary" icon="bi-x-lg" label="Annuler la modification" data-bs-dismiss="modal" />
        <x-admin.icon-action type="submit" variant="primary" icon="bi-floppy" label="Enregistrer le CV" data-submit-label />
    </div>
</form>
