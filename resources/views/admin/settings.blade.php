@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Paramètres du site</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.settings.save') }}" enctype="multipart/form-data" data-branding-form>
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label for="site-name" class="form-label">Nom du site</label>
                <input id="site-name" type="text" name="site_name" class="form-control" value="{{ old('site_name', $settings['site_name'] ?? 'Portfolio') }}">
            </div>
            <div class="col-md-6">
                <label for="site-email" class="form-label">Email</label>
                <input id="site-email" type="email" name="site_email" class="form-control" value="{{ old('site_email', $settings['site_email'] ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="site-phone" class="form-label">Téléphone</label>
                <input id="site-phone" type="text" name="site_phone" class="form-control" value="{{ old('site_phone', $settings['site_phone'] ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="site-location" class="form-label">Localisation</label>
                <input id="site-location" type="text" name="site_location" class="form-control" value="{{ old('site_location', $settings['site_location'] ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="default-locale" class="form-label">Langue par défaut</label>
                <select id="default-locale" name="default_locale" class="form-select">
                    <option value="fr" @selected(old('default_locale', $settings['default_locale'] ?? 'fr') === 'fr')>Français</option>
                    <option value="en" @selected(old('default_locale', $settings['default_locale'] ?? 'fr') === 'en')>English</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="brand-logo" class="form-label">Logo du site</label>
                <input id="brand-logo" type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp" data-brand-logo-input>
                <div class="form-text">PNG, JPG ou WebP, 5 Mo maximum. Les couleurs principales sont détectées automatiquement.</div>
                @error('logo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                <div class="d-flex flex-wrap align-items-center gap-3 mt-3">
                    <img src="{{ !empty($settings['logo_path']) ? asset('storage/'.$settings['logo_path']) : '' }}" alt="Aperçu du logo" class="admin-brand-preview" data-brand-logo-preview @if(empty($settings['logo_path'])) hidden @endif>
                    @if(!empty($settings['logo_path']))
                        <label class="form-check-label"><input class="form-check-input me-1" type="checkbox" name="remove_logo" value="1"> Retirer le logo actuel</label>
                    @endif
                </div>
                <div class="form-text mt-2" data-brand-palette-status aria-live="polite"></div>
            </div>
            <div class="col-md-6">
                <label for="primary-color" class="form-label">Couleur principale</label>
                <div class="d-flex align-items-center gap-2">
                    <input id="primary-color" type="color" name="primary_color" class="form-control form-control-color" value="{{ old('primary_color', $settings['primary_color'] ?? '#15766f') }}" data-brand-primary>
                    <span class="small text-muted">Boutons et accents</span>
                </div>
                @error('primary_color')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="accent-color" class="form-label">Couleur secondaire</label>
                <div class="d-flex align-items-center gap-2">
                    <input id="accent-color" type="color" name="accent_color" class="form-control form-control-color" value="{{ old('accent_color', $settings['accent_color'] ?? '#d8785f') }}" data-brand-accent>
                    <span class="small text-muted">Détails et surbrillances</span>
                </div>
                @error('accent_color')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        </div>
        <x-admin.icon-action type="submit" variant="primary" icon="bi-floppy" label="Enregistrer les paramètres" class="mt-4" />
    </form>

    <section class="mt-5 border-top pt-4">
        <h3 class="h5">Sauvegarde et restauration</h3>
        <p class="text-muted">Choisissez les rubriques à inclure dans le fichier JSON. Les rubriques non sélectionnées ne seront pas exportées.</p>
        <form method="GET" action="{{ route('admin.settings.backup') }}" class="border rounded p-3 mb-3">
            <fieldset>
                <legend class="form-label fs-6">Rubriques à sauvegarder</legend>
                <div class="row g-2">
                    @foreach($backupGroups as $key => $group)
                        <div class="col-sm-6 col-lg-4">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="groups[]" value="{{ $key }}" @checked(in_array($key, old('groups', array_keys($backupGroups)), true))>
                                <span class="form-check-label">{{ $group['label'] }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('groups')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                @error('groups.*')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            </fieldset>
            <button type="submit" class="btn btn-outline-primary mt-3">
                <i class="bi bi-download me-1" aria-hidden="true"></i> Télécharger la sélection
            </button>
        </form>

        <form method="POST" action="{{ route('admin.settings.restore') }}" enctype="multipart/form-data" class="border rounded p-3 bg-body-tertiary">
            @csrf
            <label for="backup-file" class="form-label">Restaurer depuis un fichier de sauvegarde</label>
            <input id="backup-file" type="file" name="backup_file" class="form-control" accept="application/json,.json" required>
            <div class="form-text">Le fichier doit être une sauvegarde JSON générée par ce système. Seules les rubriques présentes dans l’archive seront remplacées.</div>
            @error('backup_file')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
            <button type="submit" class="btn btn-outline-secondary mt-3" onclick="return confirm('Restaurer remplacera les données des rubriques présentes dans cette sauvegarde. Continuer ?')">
                <i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i> Restaurer
            </button>
        </form>
    </section>

    <section class="mt-5 border-top pt-4">
        <h3 class="h5 text-danger">Zone dangereuse</h3>
        <p class="text-muted">Supprimer les données du portfolio, y compris le profil, les projets, les articles, les réglages et les modèles de CV. Votre compte administrateur sera conservé.</p>
        <form method="POST" action="{{ route('admin.settings.reset') }}" onsubmit="return confirm('Confirmer la remise à zéro ? Toutes les données du portfolio seront supprimées. Votre compte administrateur sera conservé. Cette action est irréversible.');">
            @csrf
            <x-admin.icon-action type="submit" variant="danger" icon="bi-arrow-counterclockwise" label="Remise à zéro" />
        </form>
    </section>
</div>
@endsection
