@extends('layouts.admin')

@section('content')
@php
    $palette = $resumeTemplate->available_colors[0] ?? ['name' => 'Bleu professionnel', 'primary' => '#17324D', 'secondary' => '#0F766E'];
    $layout = $resumeTemplate->layout;
    $sidebarKeys = $layout === 'portfolio-sidebar' ? ['skills', 'languages', 'certifications', 'technologies'] : ['education', 'languages'];
    $sidebarSections = array_values(array_filter($previewSections, fn ($section) => in_array($section['key'], $sidebarKeys, true)));
    $mainSections = array_values(array_filter($previewSections, fn ($section) => ! in_array($section['key'], $sidebarKeys, true)));
    $previewPhotoUrl = $resumeTemplate->supportsPhoto() && ! empty($previewProfile['avatar_path']) ? '/storage/'.ltrim($previewProfile['avatar_path'], '/') : null;
    $previewText = [
        'language' => $locale === 'en' ? 'Language' : 'Langue',
        'length' => $locale === 'en' ? 'Length' : 'Longueur',
        'data' => $locale === 'en' ? 'Data' : 'Données',
        'demo' => $locale === 'en' ? 'Demo' : 'Démonstration',
        'my_profile' => $locale === 'en' ? 'My profile' : 'Mon profil',
        'refresh' => $locale === 'en' ? 'Refresh preview' : 'Actualiser l’aperçu',
        'contact' => $locale === 'en' ? 'Contact' : 'Contact',
        'about' => $locale === 'en' ? 'Profile' : 'À propos',
        'one_page' => $locale === 'en' ? '1 page' : '1 page',
        'two_pages' => $locale === 'en' ? '2 pages' : '2 pages',
        'full' => $locale === 'en' ? 'Complete' : 'Complet',
        'return' => $locale === 'en' ? 'Back to CV templates' : 'Retour aux modèles de CV',
        'use_template' => $locale === 'en' ? 'Use this template' : 'Utiliser ce modèle',
        'zoom' => $locale === 'en' ? 'Zoom' : 'Zoom',
    ];
@endphp
<div class="container-fluid px-lg-4 py-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div>
            <x-admin.icon-action :href="route('admin.resume-templates.index')" size="sm" variant="outline-secondary" icon="bi-arrow-left" :label="$previewText['return']" />
            <h1 class="h3 mt-2 mb-1">{{ $resumeTemplate->name }}</h1>
            <p class="text-muted mb-0">{{ $resumeTemplate->description }}</p>
        </div>
        <x-admin.icon-action :href="route('admin.resumes.index', ['template' => $resumeTemplate->id])" variant="primary" icon="bi-check-lg" :label="$previewText['use_template']" />
    </div>

    <form method="GET" action="{{ route('admin.resume-templates.preview', $resumeTemplate) }}" class="row g-2 align-items-end mb-3">
        <div class="col-auto">
            <label class="form-label mb-1" for="preview-locale">{{ $previewText['language'] }}</label>
            <select id="preview-locale" name="locale" class="form-select form-select-sm">
                <option value="fr" {{ $locale === 'fr' ? 'selected' : '' }}>Français</option>
                <option value="en" {{ $locale === 'en' ? 'selected' : '' }}>English</option>
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label mb-1" for="preview-length">{{ $previewText['length'] }}</label>
            <select id="preview-length" name="length" class="form-select form-select-sm">
                <option value="one_page" {{ $length === 'one_page' ? 'selected' : '' }}>{{ $previewText['one_page'] }}</option>
                <option value="two_pages" {{ $length === 'two_pages' ? 'selected' : '' }}>{{ $previewText['two_pages'] }}</option>
                <option value="full" {{ $length === 'full' ? 'selected' : '' }}>{{ $previewText['full'] }}</option>
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label mb-1" for="preview-data">{{ $previewText['data'] }}</label>
            <select id="preview-data" name="data" class="form-select form-select-sm">
                <option value="demo" {{ ! $isRealData ? 'selected' : '' }}>{{ $previewText['demo'] }}</option>
                @if($profile)<option value="real" {{ $isRealData ? 'selected' : '' }}>{{ $previewText['my_profile'] }}</option>@endif
            </select>
        </div>
        <div class="col-auto"><x-admin.icon-action type="submit" size="sm" variant="outline-primary" icon="bi-arrow-clockwise" :label="$previewText['refresh']" /></div>
        <div class="col-auto ms-lg-auto">
            @if($resumeTemplate->ats_friendly)
                <span class="badge text-bg-success">ATS Friendly</span>
            @else
                <span class="badge text-bg-secondary">Modèle graphique</span>
            @endif
        </div>
    </form>

    @if($resumeTemplate->ats_friendly)
        <div class="alert alert-success py-2 small">Structure linéaire, rubriques standard et texte sélectionnable. Les éléments essentiels restent lisibles par les outils de recrutement.</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 border-bottom pb-3 mb-3">
        <div class="btn-group btn-group-sm" role="group" aria-label="Format d’aperçu">
            <button class="btn btn-outline-secondary active" type="button" data-preview-size="a4">A4</button>
            <button class="btn btn-outline-secondary" type="button" data-preview-size="desktop">Desktop</button>
            <button class="btn btn-outline-secondary" type="button" data-preview-size="mobile">Mobile</button>
        </div>
        <label class="d-flex align-items-center gap-2 small" for="preview-zoom">
            <span>{{ $previewText['zoom'] }}</span>
            <input id="preview-zoom" type="range" min="0.65" max="1.2" step="0.05" value="0.8" class="form-range mb-0" style="width:140px">
            <span data-preview-zoom-label>80%</span>
        </label>
    </div>

    <style>
        .preview-stage { background: #e8ecef; border-radius: 4px; min-height: 650px; overflow: auto; padding: 28px 16px; }
        .preview-viewport { margin: 0 auto; overflow: auto; transition: width .2s ease; }
        .preview-viewport[data-size="a4"], .preview-viewport[data-size="desktop"] { width: min(100%, 850px); }
        .preview-viewport[data-size="mobile"] { width: min(100%, 410px); }
        .preview-paper-wrap { height: auto; margin: 0 auto; min-height: calc(1123px * var(--preview-zoom, .8)); width: calc(794px * var(--preview-zoom, .8)); }
        .preview-paper { background: white; box-shadow: 0 8px 24px #152c3b2b; color: #273744; font-family: Arial, sans-serif; font-size: 13px; line-height: 1.5; min-height: 1123px; padding: 58px 62px; transform: scale(var(--preview-zoom, .8)); transform-origin: top left; width: 794px; }
        .preview-paper h1 { color: var(--preview-primary); font-size: 30px; margin: 0 0 6px; }
        .preview-paper h2 { border-bottom: 2px solid var(--preview-secondary); color: var(--preview-primary); font-size: 16px; margin: 24px 0 10px; padding-bottom: 5px; text-transform: uppercase; }
        .preview-paper h3 { font-size: 14px; margin: 0; }
        .preview-paper p { margin: 5px 0; }
        .preview-paper .preview-role { color: var(--preview-secondary); font-size: 16px; font-weight: 600; }
        .preview-paper .preview-contact { color: #687782; font-size: 11px; margin-top: 10px; }
        .preview-paper .preview-entry { margin-bottom: 14px; }
        .preview-paper .preview-meta { color: #73808a; font-size: 11px; }
        .preview-paper ul { margin: 5px 0; padding-left: 19px; }
        .preview-paper.layout-modern-executive { border-left: 18px solid var(--preview-primary); }
        .preview-paper.layout-minimal { font-family: 'DejaVu Sans', Arial, sans-serif; padding: 74px 78px; }
        .preview-paper.layout-minimal h2 { border: 0; letter-spacing: 1px; }
        .preview-paper.layout-corporate { border-top: 15px solid var(--preview-primary); }
        .preview-paper.layout-international { padding-top: 48px; }
        .preview-paper.layout-international .preview-profile-grid { display: grid; gap: 24px; grid-template-columns: 1fr 2fr; }
        .preview-paper.layout-ats-clean { font-family: Arial, sans-serif; padding: 48px 68px; }
        .preview-paper.layout-ats-clean h2 { text-transform: none; }
        .preview-paper.layout-tech { border-left: 10px solid var(--preview-secondary); font-family: 'Courier New', monospace; }
        .preview-paper.layout-cybersecurity { background: #fbfcfd; border-top: 22px solid var(--preview-primary); }
        .preview-paper.layout-cybersecurity h2 { border-bottom-style: dotted; }
        .preview-paper.layout-consultant .preview-contact { border-bottom: 1px solid #dce3e7; padding-bottom: 15px; }
        .preview-paper.layout-academic { font-family: Georgia, serif; padding: 55px 80px; }
        .preview-paper.layout-academic header { text-align: center; }
        .preview-paper.layout-academic h2 { font-variant: small-caps; text-transform: none; }
        .preview-paper.layout-creative-professional { border-left: 42px solid var(--preview-secondary); padding-left: 50px; }
        .preview-paper.layout-executive-premium { border: 1px solid var(--preview-secondary); font-family: Georgia, serif; padding: 66px 74px; }
        .preview-paper.layout-executive-premium header { border-bottom: 1px solid var(--preview-secondary); padding-bottom: 20px; text-align: center; }
        .preview-paper.layout-executive-premium h2 { text-align: center; }
        .preview-paper.layout-editorial-nordic { font-family: Georgia, serif; }
        .preview-paper.layout-editorial-nordic header { border-bottom: 1px solid var(--preview-secondary); margin-bottom: 16px; padding-bottom: 14px; }
        .preview-paper.layout-editorial-nordic h2 { border: 0; border-left: 3px solid var(--preview-secondary); padding-left: 8px; }
        .preview-paper.layout-portfolio-showcase header { border-bottom: 3px solid var(--preview-secondary); margin-bottom: 16px; padding-bottom: 12px; }
        .preview-paper.layout-portfolio-showcase h2 { border: 0; border-left: 4px solid var(--preview-secondary); padding-left: 8px; }
        .preview-paper.layout-bold-header header { background: #f1f4f6; border-top: 9px solid var(--preview-primary); margin: -12px -12px 16px; padding: 16px 12px 12px; }
        .preview-paper.layout-bold-header h2 { background: #edf2f5; border-bottom-width: 1px; padding: 5px 8px; }
        .preview-paper.layout-graduate-focus { font-family: Georgia, serif; }
        .preview-paper.layout-graduate-focus header { border-bottom: 1px solid var(--preview-secondary); margin-bottom: 16px; padding-bottom: 14px; text-align: center; }
        .preview-paper.layout-graduate-focus h2 { border-bottom-style: double; font-variant: small-caps; }
        .preview-paper.layout-portfolio-sidebar, .preview-paper.layout-warm-academic { display: grid; align-items: stretch; gap: 0; grid-template-columns: 34% minmax(0, 1fr); height: auto; padding: 0; }
        .preview-paper.layout-portfolio-sidebar .preview-sidebar, .preview-paper.layout-warm-academic .preview-sidebar { float: none; margin: 0; min-height: 0; padding: 54px 18px 48px 42px; width: auto; }
        .preview-paper.layout-portfolio-sidebar .preview-sidebar { background: var(--preview-primary); color: #f5f7f5; }
        .preview-paper.layout-portfolio-sidebar .preview-main, .preview-paper.layout-warm-academic .preview-main { margin: 0; min-width: 0; }
        .preview-paper.layout-portfolio-sidebar .preview-sidebar h2 { border-color: var(--preview-secondary); color: #fff; font-size: 12px; }
        .preview-paper.layout-portfolio-sidebar .preview-sidebar h3 { color: #fff; }
        .preview-paper.layout-portfolio-sidebar .preview-sidebar .preview-meta,
        .preview-paper.layout-portfolio-sidebar .preview-sidebar p,
        .preview-paper.layout-portfolio-sidebar .preview-sidebar li { color: #e4ece8; }
        .preview-paper.layout-portfolio-sidebar .preview-sidebar-photo { display: block; width: 118px; height: 118px; margin: 0 auto 24px; border: 4px solid #fff; border-radius: 50%; object-fit: cover; }
        .preview-paper.layout-portfolio-sidebar .preview-main, .preview-paper.layout-warm-academic .preview-main { padding: 54px 42px 48px 28px; }
        .preview-paper.layout-warm-academic { background: #fffaf2; color: #514743; font-family: Georgia, serif; }
        .preview-paper.layout-warm-academic .preview-sidebar { background: #f5ece2; border-right: 1px solid #d8b4ae; }
        .preview-paper.layout-warm-academic .preview-sidebar h2,
        .preview-paper.layout-warm-academic .preview-main h1,
        .preview-paper.layout-warm-academic .preview-main h2 { color: #624b45; }
        .preview-paper.layout-warm-academic .preview-sidebar h2,
        .preview-paper.layout-warm-academic .preview-main h2 { border-color: #c77990; }
        .preview-paper.layout-warm-academic .preview-sidebar-photo { display: block; width: 100px; height: 124px; margin: 0 auto 22px; border: 3px solid #c77990; border-radius: 4px; object-fit: cover; }
        .preview-paper.layout-warm-academic .warm-marker { color: #c77990; font-size: 24px; font-weight: 700; }
        .preview-contact-block { overflow-wrap: anywhere; }
        .preview-sidebar .preview-contact-block { font-size: 11px; }
        .preview-photo { float: right; width: 82px; height: 100px; margin: 0 0 12px 16px; object-fit: cover; }
        .preview-paper.layout-editorial-nordic .preview-photo,
        .preview-paper.layout-graduate-focus .preview-photo { border-radius: 50%; }
        .preview-viewport[data-size="mobile"] .preview-paper { font-size: 11px; padding: 48px 42px; }
        @media (prefers-reduced-motion: reduce) { .preview-viewport { transition: none; } }
    </style>

    <div class="preview-stage" style="--preview-zoom: .8; --preview-primary: {{ $palette['primary'] }}; --preview-secondary: {{ $palette['secondary'] }};">
        <div class="preview-viewport" data-preview-viewport data-size="a4">
            <div class="preview-paper-wrap">
                <article class="preview-paper layout-{{ $resumeTemplate->layout }}">
                    @if(in_array($layout, ['portfolio-sidebar', 'warm-academic'], true))
                        <aside class="preview-sidebar">
                            @if($previewPhotoUrl)<img class="preview-sidebar-photo" src="{{ $previewPhotoUrl }}" alt="Portrait de {{ $previewProfile['name'] }}">@endif
                            <section>
                                <h2>{{ $previewText['contact'] }}</h2>
                                <div class="preview-contact-block">{{ implode(' · ', array_filter([$previewProfile['email'], $previewProfile['phone'], $previewProfile['location'], $previewProfile['website']])) }}</div>
                            </section>
                            @if($layout === 'portfolio-sidebar' && $previewProfile['summary'])
                                <section><h2>{{ $previewText['about'] }}</h2><p>{{ $previewProfile['summary'] }}</p></section>
                            @endif
                            @include('admin.resume-templates._preview-sections', ['sections' => $sidebarSections])
                        </aside>
                        <main class="preview-main">
                            <header>
                                @if($layout === 'warm-academic')<span class="warm-marker" aria-hidden="true">+</span>@endif
                                <h1>{{ $previewProfile['name'] ?: $resumeTemplate->name }}</h1>
                                <p class="preview-role">{{ $previewProfile['title'] }}</p>
                                @if($layout === 'warm-academic' && $previewProfile['summary'])<p>{{ $previewProfile['summary'] }}</p>@endif
                            </header>
                            @include('admin.resume-templates._preview-sections', ['sections' => $mainSections])
                        </main>
                    @else
                        <header>
                            @if($previewPhotoUrl)<img class="preview-photo" src="{{ $previewPhotoUrl }}" alt="Portrait de {{ $previewProfile['name'] }}">@endif
                            <h1>{{ $previewProfile['name'] ?: $resumeTemplate->name }}</h1>
                            <p class="preview-role">{{ $previewProfile['title'] }}</p>
                            <div class="preview-contact">{{ implode(' · ', array_filter([$previewProfile['email'], $previewProfile['phone'], $previewProfile['location'], $previewProfile['website']])) }}</div>
                            @if($previewProfile['summary'])<p class="mt-3">{{ $previewProfile['summary'] }}</p>@endif
                        </header>
                        @include('admin.resume-templates._preview-sections', ['sections' => $previewSections])
                    @endif
                </article>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const viewport = document.querySelector('[data-preview-viewport]');
    const stage = document.querySelector('.preview-stage');
    const zoom = document.getElementById('preview-zoom');
    const zoomLabel = document.querySelector('[data-preview-zoom-label]');

    document.querySelectorAll('[data-preview-size]').forEach((button) => button.addEventListener('click', () => {
        viewport.dataset.size = button.dataset.previewSize;
        document.querySelectorAll('[data-preview-size]').forEach((option) => option.classList.toggle('active', option === button));
    }));
    zoom.addEventListener('input', () => {
        stage.style.setProperty('--preview-zoom', zoom.value);
        zoomLabel.textContent = `${Math.round(Number(zoom.value) * 100)}%`;
        viewport.style.setProperty('--preview-zoom', zoom.value);
        viewport.querySelector('.preview-paper-wrap').style.minHeight = `${1123 * Number(zoom.value)}px`;
        viewport.querySelector('.preview-paper-wrap').style.width = `${794 * Number(zoom.value)}px`;
    });
});
</script>
@endpush
