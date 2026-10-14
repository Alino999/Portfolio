@extends('layouts.admin')

@section('content')
<div class="container-fluid admin-profile-page py-4">
    <header class="admin-profile-heading">
        <div>
            <span class="admin-profile-kicker">Portfolio</span>
            <h1 class="h3 mb-1">Profil</h1>
            <p class="text-muted mb-0">Gérez les informations affichées sur votre portfolio.</p>
        </div>
    </header>

    @if(session('success'))
        <div class="alert alert-success" role="status" aria-live="polite">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    <section class="admin-profile-import mb-4" aria-labelledby="profile-import-title">
        <div class="card-body">
            <div class="admin-profile-import-heading">
                <div>
                    <span class="admin-profile-kicker">Import assisté</span>
                    <h2 id="profile-import-title" class="h5 mb-1">Préremplir depuis un CV</h2>
                    <p class="text-muted small mb-0">Collez le texte ou choisissez un fichier pour extraire les informations du profil.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.profile.import') }}" enctype="multipart/form-data" class="admin-profile-import-form">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-lg-7">
                        <label class="form-label" for="profile-cv-text">Texte du CV</label>
                        <textarea id="profile-cv-text" name="cv_text" class="form-control" rows="3" placeholder="Copiez ici le contenu de votre CV..."></textarea>
                    </div>
                    <div class="col-lg-5">
                        <label class="form-label" for="profile-cv-file">Ou importer un fichier</label>
                        <input id="profile-cv-file" type="file" name="cv_file" class="form-control" accept=".txt,.md,.doc,.docx,.pdf">
                        <small class="form-text">TXT, MD, DOC, DOCX ou PDF</small>
                    </div>
                </div>
                <x-admin.icon-action type="submit" variant="outline-primary" icon="bi-file-earmark-arrow-up" label="Importer le CV" class="mt-3" />
            </form>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.profile.update') }}" class="card admin-profile-form">
        @csrf
        <div class="card-body">
            <div class="admin-profile-form-heading">
                <div>
                    <span class="admin-profile-kicker">Informations publiées</span>
                    <h2 class="h5 mb-1">Détails du profil</h2>
                    <p class="text-muted small mb-0">Les champs français et anglais alimentent les deux versions du site.</p>
                </div>
            </div>

            <fieldset class="admin-profile-section">
                <legend>Identité professionnelle</legend>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="profile-first-name">Prénom</label><input id="profile-first-name" type="text" name="first_name" class="form-control" value="{{ old('first_name', $profile->first_name) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="profile-last-name">Nom</label><input id="profile-last-name" type="text" name="last_name" class="form-control" value="{{ old('last_name', $profile->last_name) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="profile-title-fr">Titre (français)</label><input id="profile-title-fr" type="text" name="title" class="form-control" value="{{ old('title', $profile->title) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="profile-title-en">Title (English)</label><input id="profile-title-en" type="text" name="title_en" class="form-control" value="{{ old('title_en', $profile->title_en) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="profile-subtitle-fr">Sous-titre (français)</label><input id="profile-subtitle-fr" type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $profile->subtitle) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="profile-subtitle-en">Subtitle (English)</label><input id="profile-subtitle-en" type="text" name="subtitle_en" class="form-control" value="{{ old('subtitle_en', $profile->subtitle_en) }}"></div>
                </div>
            </fieldset>

            <fieldset class="admin-profile-section">
                <legend>Coordonnées et activité</legend>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="profile-email">Email</label><input id="profile-email" type="email" name="email" class="form-control" value="{{ old('email', $profile->email) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="profile-phone">Téléphone</label><input id="profile-phone" type="text" name="phone" class="form-control" value="{{ old('phone', $profile->phone) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="profile-location">Localisation</label><input id="profile-location" type="text" name="location" class="form-control" value="{{ old('location', $profile->location) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="profile-country">Pays</label><input id="profile-country" type="text" name="country" class="form-control" value="{{ old('country', $profile->country) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="profile-availability">Disponibilité</label><input id="profile-availability" type="text" name="availability" class="form-control" value="{{ old('availability', $profile->availability) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="profile-status">Statut</label><input id="profile-status" type="text" name="professional_status" class="form-control" value="{{ old('professional_status', $profile->professional_status) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="profile-experience">Années d'expérience</label><input id="profile-experience" type="number" name="years_experience" class="form-control" value="{{ old('years_experience', $profile->years_experience) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="profile-website">Site web</label><input id="profile-website" type="url" name="website" class="form-control" value="{{ old('website', $profile->website) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="profile-linkedin">LinkedIn</label><input id="profile-linkedin" type="url" name="linkedin" class="form-control" value="{{ old('linkedin', $profile->linkedin) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="profile-github">GitHub</label><input id="profile-github" type="url" name="github" class="form-control" value="{{ old('github', $profile->github) }}"></div>
                </div>
            </fieldset>

            <fieldset class="admin-profile-section">
                <legend>Présentation</legend>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="profile-summary-fr">Résumé (français)</label><textarea id="profile-summary-fr" name="summary" class="form-control" rows="5">{{ old('summary', $profile->summary) }}</textarea></div>
                    <div class="col-md-6"><label class="form-label" for="profile-summary-en">Summary (English)</label><textarea id="profile-summary-en" name="summary_en" class="form-control" rows="5">{{ old('summary_en', $profile->summary_en) }}</textarea></div>
                    <div class="col-md-6"><label class="form-label" for="profile-bio-fr">Biographie (français)</label><textarea id="profile-bio-fr" name="bio" class="form-control" rows="7">{{ old('bio', $profile->bio) }}</textarea></div>
                    <div class="col-md-6"><label class="form-label" for="profile-bio-en">Biography (English)</label><textarea id="profile-bio-en" name="bio_en" class="form-control" rows="7">{{ old('bio_en', $profile->bio_en) }}</textarea></div>
                </div>
            </fieldset>
        </div>
        <div class="admin-profile-savebar">
            <span class="text-muted small">Les modifications sont enregistrées dans le profil du site.</span>
            <x-admin.icon-action type="submit" variant="primary" icon="bi-floppy" label="Enregistrer les modifications" />
        </div>
    </form>
</div>
@endsection
