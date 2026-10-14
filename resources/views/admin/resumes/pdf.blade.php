@php
    $customization ??= $resume->customization ?? [];
    $primaryColor = preg_match('/\A#[0-9A-Fa-f]{6}\z/', $customization['primary_color'] ?? '') ? $customization['primary_color'] : '#17324D';
    $secondaryColor = preg_match('/\A#[0-9A-Fa-f]{6}\z/', $customization['secondary_color'] ?? '') ? $customization['secondary_color'] : '#0F766E';
    $fontFamily = match ($customization['font'] ?? 'sans') {
        'serif', 'Georgia' => 'DejaVu Serif',
        'mono' => 'DejaVu Sans Mono',
        'Calibri', 'Aptos', 'Inter', 'Arial', 'sans' => 'DejaVu Sans',
        default => 'DejaVu Sans',
    };
    $fontSize = max(8, min(14, (int) ($customization['font_size'] ?? 10)));
    $layout = $resume->template?->layout ?? 'classic';
    $hasPhoto = ($customization['include_photo'] ?? false) && ! empty($profile['avatar_path']);
    $avatarFile = $hasPhoto && \Illuminate\Support\Facades\Storage::disk('public')->exists($profile['avatar_path'])
        ? \Illuminate\Support\Facades\Storage::disk('public')->path($profile['avatar_path'])
        : null;
    $sidebarSectionKeys = $layout === 'portfolio-sidebar'
        ? ['skills', 'languages', 'certifications', 'technologies']
        : ['education', 'languages'];
    $sidebarSections = array_values(array_filter($sections, fn ($section) => in_array($section['key'], $sidebarSectionKeys, true)));
    $mainSections = array_values(array_filter($sections, fn ($section) => ! in_array($section['key'], $sidebarSectionKeys, true)));
    $contactItems = array_filter([$profile['phone'], $profile['email'], $profile['location'], $profile['website']]);
@endphp
<!DOCTYPE html>
<html lang="{{ $resume->locale }}">
<head>
    <meta charset="utf-8">
    <title>{{ $resume->title }}</title>
    <style>
        @page { margin: 24mm 18mm; }
        * { box-sizing: border-box; }
        body { color: #243342; font-family: DejaVu Sans, sans-serif; font-size: 10pt; line-height: 1.45; }
        h1 { color: #17324d; font-size: 25pt; margin: 0 0 3px; }
        h2 { border-bottom: 2px solid #0f766e; color: #17324d; font-size: 13pt; margin: 20px 0 9px; padding-bottom: 4px; }
        h3 { color: #17324d; font-size: 11pt; margin: 0 0 3px; }
        p { margin: 3px 0 6px; }
        .headline { color: #0f766e; font-size: 12pt; font-weight: bold; margin-bottom: 9px; }
        .contact { color: #526271; font-size: 9pt; margin-bottom: 12px; }
        .summary { border-left: 3px solid #0f766e; margin: 12px 0; padding-left: 10px; }
        .entry { margin: 0 0 12px; page-break-inside: avoid; }
        .meta { color: #64748b; font-size: 9pt; }
        ul { margin: 4px 0 0; padding-left: 18px; }
        li { margin: 2px 0; }
        .compact { font-size: 9pt; line-height: 1.3; }
        .compact h2 { margin-top: 13px; }
        .layout-modern header { border-left: 5px solid #0f766e; margin-bottom: 18px; padding: 7px 0 7px 14px; }
        .layout-modern h1, .layout-modern h2 { color: #102a43; }
        .layout-modern h2 { background: #e8f3f1; border: 0; padding: 5px 8px; }
        .layout-elegant { color: #3f3440; font-family: DejaVu Serif, serif; }
        .layout-elegant header { border-bottom: 1px solid #b58a4a; margin-bottom: 18px; padding-bottom: 14px; text-align: center; }
        .layout-elegant h1, .layout-elegant h2, .layout-elegant h3 { color: #512d3a; font-family: DejaVu Serif, serif; }
        .layout-elegant h2 { border-color: #b58a4a; }
        .layout-elegant .headline { color: #a67736; }
        .layout-creative header { background: #264653; margin: -8mm -4mm 18px; padding: 14mm 12mm; }
        .layout-creative header h1 { color: #fff; }
        .layout-creative .headline { color: #f4a261; }
        .layout-creative .contact { color: #e4edf0; }
        .layout-creative .summary { border-color: #e76f51; color: #fff; }
        .layout-creative h2 { border: 0; border-left: 4px solid #e76f51; padding: 2px 0 2px 8px; }
        .layout-creative h3 { color: #264653; }
        .layout-modern-executive header { border-left: 7px solid #42a5a0; padding-left: 14px; }
        .layout-minimal h2 { border: 0; font-size: 12pt; margin-top: 14px; }
        .layout-corporate header { border-top: 8px solid #34495e; padding-top: 12px; }
        .layout-international .contact { border-bottom: 1px solid #d6dde2; padding-bottom: 10px; }
        .layout-ats-clean h2 { border-bottom: 1px solid #444; text-transform: none; }
        .layout-tech header { border-left: 8px solid #16a6a1; padding-left: 12px; }
        .layout-cybersecurity h2 { border-bottom-style: dotted; }
        .layout-consultant header { border-bottom: 1px solid #b58a4a; padding-bottom: 12px; }
        .layout-academic { font-family: DejaVu Serif, serif; }
        .layout-academic header { text-align: center; }
        .layout-academic h2 { font-variant: small-caps; }
        .layout-creative-professional header { border-left: 18px solid #e76f51; padding: 10px 0 10px 14px; }
        .layout-executive-premium header { border-bottom: 1px solid #a38d68; padding-bottom: 16px; text-align: center; }
        .layout-executive-premium h2 { text-align: center; }
        .layout-editorial-nordic { font-family: DejaVu Serif, serif; }
        .layout-editorial-nordic header { border-bottom: 1px solid #b88755; margin-bottom: 16px; padding-bottom: 14px; }
        .layout-editorial-nordic h2 { border: 0; border-left: 3px solid #b88755; padding-left: 8px; }
        .layout-portfolio-showcase header { border-bottom: 3px solid #d97961; margin-bottom: 16px; padding-bottom: 12px; }
        .layout-portfolio-showcase h2 { background: #eef3f4; border: 0; border-left: 4px solid #d97961; padding: 5px 8px; }
        .layout-bold-header header { background: #f1f4f6; border-top: 9px solid #1f3a5f; margin-bottom: 16px; padding: 12px; }
        .layout-bold-header h2 { background: #edf2f5; border-bottom-width: 1px; padding: 5px 8px; }
        .layout-graduate-focus { font-family: DejaVu Serif, serif; }
        .layout-graduate-focus header { border-bottom: 1px solid #b18b5e; margin-bottom: 16px; padding-bottom: 14px; text-align: center; }
        .layout-graduate-focus h2 { border-bottom-style: double; font-variant: small-caps; }
        .resume-photo { display: block; float: right; height: 28mm; margin: 0 0 8mm 8mm; object-fit: cover; width: 23mm; }
        .layout-elegant .resume-photo, .layout-academic .resume-photo, .layout-graduate-focus .resume-photo { border-radius: 50%; }
        .resume-sidebar { float: left; margin-left: -18mm; padding: 16mm 6mm 14mm 15mm; width: 34%; }
        .resume-sidebar h2 { font-size: 11pt; margin-top: 14px; text-transform: uppercase; }
        .resume-sidebar h3 { font-size: 9.5pt; }
        .resume-sidebar .entry { margin-bottom: 8px; }
        .resume-sidebar .meta, .resume-sidebar p { font-size: 8.5pt; }
        .resume-sidebar .resume-section-title { color: inherit !important; }
        .resume-main { margin-left: 38%; padding-top: 3mm; }
        .resume-main header { margin-bottom: 8mm; }
        .resume-sidebar-photo { border: 3px solid #fff; border-radius: 50%; float: none; height: 42mm; margin: 0 auto 10mm; width: 42mm; }
        body.layout-portfolio-sidebar, body.layout-warm-academic { position: relative; }
        body.layout-portfolio-sidebar::before, body.layout-warm-academic::before { content: ""; left: -18mm; position: fixed; top: -24mm; bottom: -24mm; width: 70mm; z-index: 0; }
        body.layout-portfolio-sidebar::before { background: #303734; }
        body.layout-warm-academic { background: #fffaf2; }
        body.layout-warm-academic::before { background: #f5ece2; border-right: 1px solid #d8b4ae; }
        body.layout-portfolio-sidebar > .resume-sidebar,
        body.layout-portfolio-sidebar > .resume-main,
        body.layout-warm-academic > .resume-sidebar,
        body.layout-warm-academic > .resume-main { position: relative; z-index: 1; }
        .screen-actions { position: relative; z-index: 2; }
        .layout-portfolio-sidebar .resume-sidebar { color: #f5f7f5; }
        .layout-portfolio-sidebar .resume-sidebar h2, .layout-portfolio-sidebar .resume-sidebar h3 { color: #fff; }
        .layout-portfolio-sidebar .resume-sidebar .meta, .layout-portfolio-sidebar .resume-sidebar p { color: #e0e8e4; }
        .layout-portfolio-sidebar .resume-sidebar .resume-section-title { border-bottom: 1px solid #7aa693; padding-bottom: 3px; }
        .layout-portfolio-sidebar .resume-main h1 { font-size: 27pt; line-height: 1.05; }
        .layout-warm-academic { color: #514743; font-family: DejaVu Sans, sans-serif; }
        .layout-warm-academic .resume-sidebar { color: #514743; padding-right: 5mm; }
        .layout-warm-academic .resume-sidebar h2 { border-color: #c77990; color: #624b45; }
        .layout-warm-academic .resume-main h1, .layout-warm-academic .resume-main h2 { color: #624b45; }
        .layout-warm-academic .resume-main .resume-section-title { border-color: #c77990; }
        .layout-warm-academic .warm-marker { color: #c77990; font-size: 20pt; font-weight: bold; margin-right: 6px; }
        .layout-warm-academic .resume-sidebar-photo { border-color: #c77990; border-radius: 4px; height: 34mm; width: 28mm; }
        .resume-contact-list { overflow-wrap: anywhere; }
        .screen-actions { align-items: center; background: #fff; border-bottom: 1px solid #dce3e8; display: flex; gap: 8px; justify-content: flex-end; margin: -12mm -18mm 18px; padding: 10px 18mm; }
        .screen-actions a { border: 1px solid #cad3da; border-radius: 4px; color: #243342; font-family: sans-serif; font-size: 9pt; padding: 6px 10px; text-decoration: none; }
        @media print { .screen-actions { display: none; } }
    </style>
</head>
    <body class="layout-{{ $resume->template?->layout ?? 'classic' }} {{ $resume->length === 'one_page' ? 'compact' : '' }}" style="font-family:{{ $fontFamily }}, sans-serif; font-size:{{ $fontSize }}pt;">
    @if($screenActions ?? true)
        <nav class="screen-actions">
            <a href="{{ route('admin.resumes.index') }}">Retour à la liste</a>
            <a href="{{ route('admin.resumes.edit', $resume) }}">Modifier</a>
            <a href="{{ route('admin.resumes.download', $resume) }}">Télécharger</a>
        </nav>
    @endif
    @if(in_array($layout, ['portfolio-sidebar', 'warm-academic'], true))
        <aside class="resume-sidebar">
            @if($avatarFile && file_exists($avatarFile))
                <img class="resume-photo resume-sidebar-photo" src="{{ $avatarFile }}" alt="Portrait de {{ $profile['name'] }}">
            @endif
            <section class="resume-sidebar-section">
                <h2 class="resume-section-title">{{ $resume->locale === 'en' ? 'Contact' : 'Contact' }}</h2>
                <div class="resume-contact-list">
                    @foreach($contactItems as $contactItem)<p>{{ $contactItem }}</p>@endforeach
                </div>
            </section>
            @if($layout === 'portfolio-sidebar' && $profile['summary'])
                <section class="resume-sidebar-section">
                    <h2 class="resume-section-title">{{ $resume->locale === 'en' ? 'Profile' : 'À propos' }}</h2>
                    <p>{{ $profile['summary'] }}</p>
                </section>
            @endif
            @foreach($sidebarSections as $resumeSection)
                @include('admin.resumes._section', ['resumeSection' => $resumeSection, 'primaryColor' => $primaryColor, 'secondaryColor' => $secondaryColor])
            @endforeach
        </aside>
        <main class="resume-main">
            <header>
                @if($layout === 'warm-academic')<span class="warm-marker" aria-hidden="true">+</span>@endif
                <h1 style="color:{{ $primaryColor }};">{{ $profile['name'] ?: $resume->title }}</h1>
                @if($profile['title'])<div class="headline" style="color:{{ $secondaryColor }};">{{ $profile['title'] }}</div>@endif
                @if($profile['summary'] && $layout === 'warm-academic')<p class="summary">{{ $profile['summary'] }}</p>@endif
            </header>
            @foreach($mainSections as $resumeSection)
                @include('admin.resumes._section', ['resumeSection' => $resumeSection, 'primaryColor' => $primaryColor, 'secondaryColor' => $secondaryColor])
            @endforeach
        </main>
    @else
        <header>
            @if($avatarFile && file_exists($avatarFile))
                <img class="resume-photo" src="{{ $avatarFile }}" alt="Portrait de {{ $profile['name'] }}">
            @endif
            <h1 style="color:{{ $primaryColor }};">{{ $profile['name'] ?: $resume->title }}</h1>
            @if($profile['title'])<div class="headline" style="color:{{ $secondaryColor }};">{{ $profile['title'] }}</div>@endif
            <div class="contact">{{ implode(' · ', array_filter([$profile['email'], $profile['phone'], $profile['location'], $profile['website']])) }}</div>
            @if($profile['summary'])<p class="summary">{{ $profile['summary'] }}</p>@endif
        </header>
        @foreach($sections as $resumeSection)
            @include('admin.resumes._section', ['resumeSection' => $resumeSection, 'primaryColor' => $primaryColor, 'secondaryColor' => $secondaryColor])
        @endforeach
    @endif
</body>
</html>
